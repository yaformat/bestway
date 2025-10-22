export default [
    {
        title: 'Главная', to: {name: 'index'}, icon: {icon: 'mdi-home-outline'},
    },
    {
        title: 'Отели', to: {path: '/hotels', name: 'hotel'}, icon: {icon: 'mdi-hotel'},
    },
    {
        title: 'Туры', to: {path: '/tours', name: 'tour'}, icon: {icon: 'mdi-palm-tree' },
    },
    {
        title: 'Экскурсии', to: {path: '/excursions', name: 'excursion'}, icon: {icon: 'mdi-map-marker-outline'},
    },
    {
        title: 'Трансферы', to: {path: '/transfers', name: 'transfer'}, icon: {icon: 'mdi-car-outline'},
    },
    {
        title: 'Направления', to: {path: '/directions', name: 'direction'}, icon: {icon: 'mdi-earth'},
    },
    {
        title: 'Настройки', to: {path: '/settings', name: 'settings'}, icon: { icon: 'mdi-cog' },
        children: [
            {
              title: 'Домены',
              to: { path: '/settings/domains', name: 'settings-domains' }
            },
          ]
    },
    {title: 'Пользователи', to: {path: '/users', name: 'users'}, icon: {icon: 'mdi-account'}},
]
