<?php

namespace psrm\controllers;

use Exception;
use GUMP;
use psrm\PSRM;
use psrm\utils\View;
use psrm\models\Donation as DonationSettings;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;
use UnexpectedValueException;

class Donation
{
    protected $settings;

    public function __construct()
    {
        $this->settings = DonationSettings::load();
        add_shortcode(PSRM::$slug . '-donation-form', [$this, 'display_donation_form']);
        add_action('wp_ajax_process_donation', [$this, 'process_donation']);
        add_action('wp_ajax_nopriv_process_donation', [$this, 'process_donation']);
        add_action('wp_ajax_post_donation', [$this, 'postDonation']);
        add_action('wp_ajax_nopriv_post_donation', [$this, 'postDonation']);
    }

    public function display_donation_form()
    {
        return new View('donation-form', [
            'donation_amounts' => $this->settings->getOption(DonationSettings::DonationAmounts, DonationSettings::Group),
            'donation_funds' => $this->settings->getOption(DonationSettings::DonationFunds, DonationSettings::Group),
            'allow_custom_amount' => $this->settings->getOption(DonationSettings::AllowCustomAmountOptionName, DonationSettings::Group),
            'custom_donation_floor' => $this->settings->getOption(DonationSettings::CustomAmountFloorOptionName, DonationSettings::Group),
        ]);
    }

    public function postDonation()
    {
        // Set your secret key. Remember to switch to your live secret key in production!
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        Stripe::setApiKey($this->settings->getOption(DonationSettings::StripeSecretKeyOptionName, DonationSettings::Group));

        // If you are testing your webhook locally with the Stripe CLI you
        // can find the endpoint's secret by running `stripe listen`
        // Otherwise, find your endpoint's secret in your webhook settings in the Developer Dashboard
        $endpoint_secret = $this->settings->getOption(DonationSettings::StripeWebhookSigningSecret, DonationSettings::Group);

        $payload = file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try
        {
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        }
        catch (UnexpectedValueException $e)
        {
            // Invalid payload
            wp_die('', '', 400);
        }
        catch (SignatureVerificationException $e)
        {
            // Invalid signature
            wp_die('', '', 400);
        }

        // Handle the event
        switch ($event->type)
        {
            case 'checkout.session.completed':
                /** @var Session $checkoutSession */
                $checkoutSession = $event->data->object;
                $metaData = $checkoutSession->metadata->toArray();
                if (!empty($metaData))
                {
                    $paymentIntentId = $checkoutSession->payment_intent;
                    PaymentIntent::update($paymentIntentId, [
                        'metadata' => $metaData,
                    ]);

                    wp_mail(
                        $this->settings->getOption(DonationSettings::EmailSuccessfulDonationOptionName, DonationSettings::Group),
                        "Successful donation to the {$metaData['Fund']}",
                        sprintf('Successful donation! View this transaction in Stripe: %s', $this->settings->getOption(DonationSettings::StripeDashboardUrlOptionName, DonationSettings::Group) . $paymentIntentId)
                    );
                }
        }

        wp_die('', '', 200);
    }

    public function process_donation()
    {
        $data = $this->doValidation($_POST, $this->settings->getOption(DonationSettings::CustomAmountFloorOptionName, DonationSettings::Group));

        $donation_amounts = $this->settings->getOption(DonationSettings::DonationAmounts, DonationSettings::Group);

        if ($data['success'] && ($data['result']['amount'] == 'custom' || isset($donation_amounts[$data['result']['amount']])))
        {
            if ($data['result']['amount'] == 'custom')
            {
                $donation_amount = $data['result']['customAmount'];
            }
            else
            {
                $donation_amount = $donation_amounts[$data['result']['amount']];
            }

            $fund = $data['result']['fund'];
            $subscription = false; // $data['result']['subscription'] === 'true'; // later... not now.
            $payFees = $data['result']['pay_fees'] === 'true';

            $lineItems = [];
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => "Donation",
                    ],
                    'unit_amount' => $donation_amount * 100,
                ],
                'quantity' => 1,
            ];

            if ($payFees)
            {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => "Processing fee - THANK YOU!",
                        ],
                        'unit_amount' => round(((($donation_amount + 0.3) / (1 - 0.029)) - $donation_amount), 2) * 100,
                    ],
                    'quantity' => 1,
                ];
            }

            if ($subscription)
            {
                for ($i = 0; $i < count($lineItems); $i++)
                {
                    $lineItems[$i]['price_data']['recurring'] = ['interval' => 'month'];
                }
            }

            try
            {
                Stripe::setApiKey($this->settings->getOption(DonationSettings::StripeSecretKeyOptionName, DonationSettings::Group));
                $sessionParams = [
                    'billing_address_collection' => 'required',
                    'metadata' => [
                        'Fund' => $fund,
                    ],
                    'payment_method_types' => ['card'],
                    'line_items' => $lineItems,
                    'mode' => $subscription ? 'subscription' : 'payment',
                    'success_url' => $this->settings->getOption(DonationSettings::DonationRedirectSuccess, DonationSettings::Group),
                    'cancel_url' => $this->settings->getOption(DonationSettings::DonationRedirectCancel, DonationSettings::Group),
                ];

                if (!$subscription)
                {
                    $sessionParams['submit_type'] = 'donate';
                }

                $session = Session::create($sessionParams);
            }
            catch (Exception $e)
            {
                wp_die(wp_json_encode(['success' => false, 'message' => $e->getMessage()]), '', 500);
            }

            wp_die(wp_json_encode(['success' => true, 'session_id' => $session->id]));
        }

        wp_die(wp_json_encode(['success' => false, 'message' => $data['result']]), '', 400);
    }

    protected function doValidation($data, $donation_min)
    {
        $gump = new GUMP();

        $data = $gump->sanitize($data);

        $gump->validation_rules([
            'amount' => 'required',
            'fund' => 'required',
            'customAmount' => 'integer|min_numeric,' . $donation_min,
            'subscription' => 'required',
            'pay_fees' => 'required',
        ]);

        try
        {
            $validated_data = $gump->run($data);
        }
        catch (Exception $e)
        {
            return [
                'success' => false,
                'result' => $e->getMessage(),
            ];
        }

        if ($validated_data === false)
        {
            return [
                'success' => false,
                'result' => $gump->get_errors_array(),
            ];
        }
        else
        {
            return [
                'success' => true,
                'result' => $validated_data,
            ];
        }
    }
}
