var elixir = require('laravel-elixir');

elixir(function (mix) {
    mix
        .sass([
                'front/base.scss',
                'front/daily-hours.scss',
                'front/donation.scss',
                'front/service-alerts.scss'
            ],
            'public/css/main.css')
        .sass([
                'admin/admin.scss',
                'admin/donation-settings.scss'
            ],
            'public/css/admin.css')
        .scripts([
                'front/components/*.js',
                'front/*.js'
            ],
            'public/js/main.js')
        .scripts([
                'sortablejs/Sortable.js'
            ],
            'public/js/sortable.js',
            'node_modules'
        )
        .scripts([
                'resources/assets/js/admin/*.js'
            ],
            'public/js/admin.js'
        )
        .scripts([
            'sortable.js',
            'admin.js'
        ], 'public/js/admin.js', 'public/js')
});