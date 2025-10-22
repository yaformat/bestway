export default [
    {
        title: 'Главная', to: {name: 'index'}, icon: {icon: 'mdi-home-outline'},
    },
    {
        title: 'Отели', to: {path: '/hotel', name: 'hotel'}, icon: {icon: 'mdi-hotel'},
    },
    {
        title: 'Туры', to: {path: '/tour', name: 'tour'}, icon: {icon: 'mdi-palm-tree' },
    },
    {
        title: 'Экскурсии', to: {path: '/excursion', name: 'excursion'}, icon: {icon: 'mdi-map-marker-outline'},
    },
    {
        title: 'Трансферы', to: {path: '/transfer', name: 'transfer'}, icon: {icon: 'mdi-car-outline'},
    },
    {
        title: 'Направления', to: {path: '/direction', name: 'direction'}, icon: {icon: 'mdi-earth'},
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
