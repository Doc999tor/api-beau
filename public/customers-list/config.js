var _config = {
  defaultPicture: '../public/customers-list/media/clients/default_picture',
  listLimit: 20,
  translations: {
    main_title: 'Customer database',
    create_group: 'Create group',
    add_client: 'Add client',
    close_visits: 'Close visits',
    inactive_title: 'Inactive',
    groups_title: 'Popular groups',
    show_all: 'Show All',
    header_groups_page: 'All groups',
    header_inactive_page: 'They have not visited for a long time',
    header_schedule_page: 'Close visits',
    customer_actions: 'Cancel',
    dates: {
      weekdays: {
        'Monday': 'Monday',
        'Tuesday': 'Tuesday',
        'Wednesday': 'Wednesday',
        'Thursday': 'Thursday',
        'Friday': 'Friday',
        'Saturday': 'Saturday',
        'Sunday': 'Sunday'
      },
      days: {
        'Yesterday': 'Yesterday ',
        'Tommorow': 'Tommorow ',
        'Today': 'Today '
      }
    }
  },
  urls: {
    base: 'http://api.bewebmaster.co.il',
    clientsList: 'http://api.bewebmaster.co.il/customers-list/clients',
    checkedClients: 'http://api.bewebmaster.co.il/customers-list/clients?ids=',
    clientsListLimit: '?limit=',
    clientsListOffset: '&offset=',
    clientsSearch: '?q=',
    timeoutLoading: 1000,
    unloadingBegins: 10,
    groups: '../public/customers-list/media/groups/',
    schedule: '../public/customers-list/media/clients/',
    inactive: '../public/customers-list/media/clients/',
    clients: '../public/customers-list/media/clients/'
  },
  schedule: {
    linkToAll: '/schedule',
    users: [
      {
        id: 1,
        name: 'Ahuva Ben Shoshan',
        last_appoinment: '2017-10-01 15:15',
        phone: '054-4668824'
      },
      {
        id: 2,
        name: 'Aviel Gardi Ben Ahmed Ben Amit',
        last_appoinment: '2017-10-02 17:00',
        phone: '054-4668824'
      },
      {
        id: 3,
        name: 'Shulamit Nathan',
        last_appoinment: '2017-10-03 11:30',
        phone: '054-4668824'
      },
      {
        id: 2,
        name: 'Ahuva Ben Shoshan',
        last_appoinment: '2017-10-04 09:00',
        phone: '054-4668824'
      },
      {
        id: 2,
        name: 'Aviel Gardi Ben Ahmed Ben Amit',
        last_appoinment: '2017-10-08 11:45',
        phone: '054-4668824'
      },
      {
        id: 2,
        name: 'Shulamit Nathan',
        last_appoinment: '2017-10-08 14:00',
        phone: '054-4668824'
      },
      {
        id: 4,
        name: 'Ahuva Ben Shoshan',
        last_appoinment: '2017-10-10 15:00',
        phone: '054-4668824'
      },
      {
        id: 5,
        name: 'Ahuva Ben Shoshan2',
        last_appoinment: '2017-10-10 15:15',
        phone: '054-4668824'
      },
      {
        id: 5,
        name: 'Ahuva Ben Shoshan',
        last_appoinment: '2017-10-11 16:45',
        phone: '054-4668824'
      }
    ]
  },
  inactive: {
    linkToAll: '/inactive',
    users: [
      {
        id: 4,
        name: 'Avishai Schusterman',
        last_appoinment: '2017-10-11 10:30',
        phone: '054-4668824'
      },
      {
        id: 5,
        name: 'Naama Cohen',
        last_appoinment: '2017-10-13 09:30',
        phone: '054-4668824'
      },
      {
        id: 6,
        name: 'Shoshan Gabai',
        last_appoinment: '2017-10-13 17:00',
        phone: '054-4668824'
      },
      {
        id: 4,
        name: 'Ahuva Ben Shoshan',
        last_appoinment: '2017-10-15 12:30',
        phone: '054-4668824'
      },
      {
        id: 5,
        name: 'Ahuva Ben Shoshan',
        last_appoinment: '2017-10-15 15:45',
        phone: '054-4668824'
      },
      {
        id: 5,
        name: 'Ahuva Ben Shoshan',
        last_appoinment: '2017-10-15 09:45',
        phone: '054-4668824'
      },
      {
        id: 5,
        name: 'Ahuva Ben Shoshan',
        last_appoinment: '2017-10-18 12:30',
        phone: '054-4668824'
      },
      {
        id: 5,
        name: 'Ahuva Ben Shoshan',
        last_appoinment: '2017-10-18 11:45',
        phone: '054-4668824'
      },
      {
        id: 5,
        name: 'Ahuva Ben Shoshan',
        last_appoinment: '2017-10-19 10:00',
        phone: '054-4668824'
      }
    ]
  },
  groups: {
    linkToAll: '/groups',
    groups: [
      {
        id: 1,
        name: 'Were born this month',
        amount: '72'
      },
      {
        id: 2,
        name: 'Preferred Customers',
        amount: '17'
      },
      {
        id: 3,
        name: 'They did not pay',
        amount: '8'
      },
      {
        id: 3,
        name: 'They did not pay',
        amount: '8'
      },
      {
        id: 3,
        name: 'They did not pay',
        amount: '8'
      },
      {
        id: 3,
        name: 'They did not pay',
        amount: '8'
      }
    ]
  },
  clients: {
    linkToAll: '/clients',
    clients: [{
      id: 1,
      name: 'Ahuva Ben Shoshan',
      last_appoinment: '2017-10-19 15:45',
      phone: '054-4668824'
    },
    {
      id: 2,
      name: 'Aviel Gardi Ben Ahmed Ben Amit',
      last_appoinment: '2017-10-19 15:00',
      phone: '054-4668824'
    },
    {
      id: 3,
      name: 'Shulamit Nathan',
      last_appoinment: '2017-10-20 14:00',
      phone: '054-4668824'
    },
    {
      id: 2,
      name: 'Ahuva Ben Shoshan',
      last_appoinment: '2017-10-20 15:00',
      phone: '054-4668824'
    },
    {
      id: 2,
      name: 'Aviel Gardi Ben Ahmed Ben Amit',
      last_appoinment: '2017-10-23 11:30',
      phone: '054-4668824'
    },
    {
      id: 2,
      name: 'Shulamit Nathan',
      last_appoinment: '2017-10-23 16:45',
      phone: '054-4668824'
    },
    {
      id: 4,
      name: 'Ahuva Ben Shoshan',
      last_appoinment: '2017-10-24 10:30',
      phone: '054-4668824'
    },
    {
      id: 5,
      name: 'Ahuva Ben Shoshan',
      last_appoinment: '2017-10-24 16:00',
      phone: '054-4668824'
    },
    {
      id: 5,
      name: 'Ahuva Ben Shoshan',
      last_appoinment: '2017-10-29 09:30',
      phone: '054-4668824'
    },
    {
       id: 5,
       name: 'aaaaaaaaaaaaaaaaaaaa',
       last_appoinment: '2017-10-29 10:30',
       phone: '054-4668824'
    },
    {
      id: 1,
      name: 'Ahuva Ben Shoshan',
      last_appoinment: '2017-10-30 09:45',
      phone: '054-4668824'
    },
    {
      id: 2,
      name: 'Aviel Gardi Ben Ahmed Ben Amit',
      last_appoinment: '2017-10-30 09:00',
      phone: '054-4668824'
    },
    {
      id: 3,
      name: 'Shulamit Nathan',
      last_appoinment: '2017-10-31 16:00',
      phone: '054-4668824'
    },
    {
      id: 2,
      name: 'Ahuva Ben Shoshan',
      last_appoinment: '2017-10-31 10:00',
      phone: '054-4668824'
    },
    ]
  }
}

