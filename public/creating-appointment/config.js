var _config = {
  routing: [
    {
      path: '/choosing-client',
      urlQuery: ['client_id']
    }, {
      path: '/selecting-procedure',
      urlQuery: ['services']
    },
    {
      path: '/date-selection',
      urlQuery: ['start']
    },
    {
      path: '/choosing-worker',
      urlQuery: ['worker_id']
    }, {
      path: '/summary',
      urlQuery: ['end', 'worker_id']
    }, {
      path: '/meeting',
      urlQuery: ['date', 'start', 'worker_id']
    }, {
      path: '/break',
      urlQuery: ['date', 'start', 'worker_id']
    }, {
      path: '/vacation',
      urlQuery: ['date', 'start', 'worker_id']
    }, {
      path: '/error404',
      urlQuery: []
    }
  ],
  redirectToCurrentPage: {
    'client_id|date|time': '/selectingdate',
    'client_id|date': '/procedureselection',
    'worker_id|date|time': '/procedureselection',
    'client_id': '/selectingdate'
  },
  personalEvents: {
    menu: [
      {
        href: '/meeting',
        name: 'meeting'
      }, {
        href: '/break',
        name: 'break'
      }, {
        href: '/vacation',
        name: 'vacation'
      }
    ]
  },
  translations: {
    routing: {
      '/choosing-client': 'Customer choice',
      '/selecting-procedure': 'Choice of procedure',
      '/date-selection': 'Date Selection',
      '/choosing-worker': 'Worker choice',
      '/summary': 'Summary'
    },
    global: {
      describe_the_break: 'describe the break',
      describe_the_meeting: 'describe the meeting',
      topic_of_the_meeting: 'topic of the meeting',
      all_day: 'All day',
      start: 'Start',
      end_of_leave: 'end of leave',
      calculated_field: 'calculated field',
      describe_the_vacation: 'describe the vacation',
      send_link: 'Send a link for filling up personal details.',
      createUser: 'create user',
      enter_phone_number: 'Enter a phone number...',
      duration: 'Duration',
      price: 'Price',
      enter_address: 'Enter the address...',
      address_search: 'Address search',
      procedure_search: 'search for a procedure',
      add_client: 'Add a client',
      address: 'Address',
      minutes: 'min',
      hryvnia: 'hryvnia',
      save: 'Save',
      submit: 'submit',
      skip: 'Skip',
      name: 'Name',
      amount: 'amount',
      next: 'Next',
      back: 'Back',
      from: 'from',
      at: 'at'
    },
    header: {
      defaultUser: 'default user',
      choice_of_procedures: 'procedure catalog'
    },
    addNewProcedures: {
      durationValue: 30,
      viewDurationValue: '30m',
      enter_procedure_name: 'Enter procedure name...',
      doesnt_exist_procedure: 'No such procedure, fill up the form',
      select_color: 'Select color',
      create_procedure_next: 'Create procedure',
      color_procedures: [
        '#00ffff',
        '#808080',
        '#000000',
        '#fff',
        '#00ff00',
        '#ff0000',
        '#00bfff',
        '#ff00ff',
        '#0000ff',
        '#ffff00',
        '#ffa500',
        '#e6e6fa'
      ]
    },
    bottomnav: {
      sent_notifications: "Reminders or notifications won't be sent to this client",
      number_not_filled: 'Phone number is not filled'
    },
    selectedProcedures: {
      selected_procedures: 'Selected procedures',
      price: 'pr',
      duration: 'dur'
    },
    frequentProcedures: {
      frequent_procedures: 'Frequent procedures'
    },
    remindersTimeService: {
      less_than_day: 'Less than a day',
      "in": 'In',
      before: 'before',
      days: 'days',
      day: 'day',
      week: 'week',
      more_than_week: 'More than week'
    },
    selectingReminders: {
      summary: 'Summary',
      notation: 'Notation',
      enter_a_note: 'Enter a note...',
      cancel: 'Cancel',
      appoint: 'Appoint',
      chosen_procedures: 'Chosen procedures',
      repeating_queue: 'Repeating queue',
      meeting_on_the_road: 'Meeting on the road',
      automatic_reminders: 'enable / disable automatic reminders'
    },
    categoriesList: {
      doesnt_exist_customers: "Doesn't exist in customers list, please enter his name",
      category: 'Category'
    },
    clientsList: {
      name: 'Name',
      address: 'Address',
      phone: 'Phone',
      send_link_successfully: 'The link is sent successfully.',
      send_link: 'Send a link for filling up personal details',
      enter_first_name: 'Enter first name...',
      enter_phone_number: 'Enter phone number...',
      enter_address: 'Enter address...',
      next_visit: 'Next visit',
      last_visit: 'Last visit',
      create_usr_back: 'cancel',
      create_usr_next: 'create',
      add_client: 'add client'
    },
    topnav: {
      queue_order: 'Queue order',
      personal_meetings: 'Personal meetings'
    },
    lastAppoinment: {
      last: 'Last',
      in: 'In',
      days: 'days',
      days_ago: 'days ago',
      in_2_weeks: 'In 2 weeks',
      in_3_weeks: 'In 3 weeks',
      in_4_weeks: 'In 4 weeks',
      two_weeks_ago: '2 weeks ago',
      three_weeks_ago: '3 weeks ago',
      four_weeks_ago: '4 weeks ago',
      month: 'month',
      month_ago: 'month ago',
      next_year: 'Next year',
      last_year: 'Last year',
      years: 'years',
      years_ago: 'years ago'
    },
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
    base: 'https://api.bewebmaster.co.il',
    profile_image_path: '/image',
    baseImg: 'https://api.bewebmaster.co.il/public/creating-appointment/img/',
    adress: 'https://maps.googleapis.com/maps/api/geocode/json',
    timeoutLoading: 1000,
    defaulftDebounceTime: 300,
    unloadingBegins: 10,
    groups: './public/creating-appointment/media/groups/',
    schedule: './public/creating-appointment/media/clients/',
    clients: './public/creating-appointment/media/clients/',
    static: './public/creating-appointment/media/',
    proceduresBi: 'catalog/services/bi',
    procedures: 'catalog/services',
    add_client_link: ''
  },
  reminders: [
    72000, 144000, 360000
  ],
  defaultPicture: './public/creating-appointment/media/clients/default_picture.png',
  clients: {
    linkToAll: '/clients',
    clients: [
      {
        id: 1,
        profile_image: '/123.jpg',
        name: 'Jaira Salomons',
        last_appoinment: '2017-11-14 10:45',
        address: "ביאליק, רמת גן, ישראל",
        phone: '052-1377867'
      }, {
        id: 2,
        profile_image: '/123.jpg',
        name: 'Gabi Rabinowicz',
        last_appoinment: '2017-11-24 13:15',
        address: "ישראל מסלנט, תל אביב יפו, ישראל",
        phone: '058-0365159'
      }, {
        id: 3,
        profile_image: '/123.jpg',
        name: 'Jeremie Goulston',
        last_appoinment: '2017-12-15 12:45',
        address: "ישראל מסלנט, תל אביב יפו, ישראל",
        phone: '058-0057064'
      }, {
        id: 6,
        profile_image: '/123.jpg',
        name: 'Isaac Margolin',
        last_appoinment: '2017-10-22 15:30',
        address: "וינה, אוסטריה 9",
        phone: '06-70711897'
      }, {
        id: 7,
        profile_image: '/123.jpg',
        name: 'Alon Duzy',
        last_appoinment: '2017-10-06 09:30',
        address: "12, Oosterstraat 2, 7514 DZ Enschede, הולנד ",
        phone: '057-0574836'
      }, {
        id: 11,
        profile_image: '/123.jpg',
        name: 'Jorie Machuv',
        last_appoinment: '2017-11-03 13:30',
        address: "דרך זאב ז'בוטינסקי 86, רמת גן, ישראל",
        phone: '09-5872338'
      }, {
        id: 35,
        profile_image: '/123.jpg',
        name: 'Jaques Shkolnik',
        last_appoinment: '2017-12-25 11:30',
        address: "דרך זאב ז'בוטינסקי 86, רמת גן, ישראל",
        phone: '08-93414837'
      }, {
        id: 38,
        profile_image: '/123.jpg',
        name: 'Aitan Nenbauer',
        last_appoinment: '2017-12-03 12:15',
        address: "שאול המלך 8, קרית אונו, ישראל",
        phone: '059-0817216'
      }, {
        id: 46,
        profile_image: '/123.jpg',
        name: 'Zacharias Horowitz',
        last_appoinment: '2017-12-07 16:30',
        address: "שאול המלך 8, קרית אונו, ישראל",
        phone: '01-8335057'
      }, {
        id: 46,
        profile_image: '/123.jpg',
        name: 'Johan Levinstein',
        last_appoinment: '2017-11-26 10:30',
        address: "ההסתדרות, אשקלון, ישראל",
        phone: '0677223522'
      }, {
        id: 51,
        profile_image: '/123.jpg',
        name: 'Amram Blomstein',
        last_appoinment: '2017-12-26 14:15',
        address: "שדרות דוד בן גוריון 15, אשקלון, 7828118, ישראל",
        phone: '051-8746727'
      }, {
        id: 56,
        profile_image: '/123.jpg',
        name: 'Shimshon Yoffey',
        last_appoinment: '2017-12-08 13:00',
        address: "איטליה הקטנה, ניו יורק, 10013, ארצות הברית",
        phone: '054-4921721'
      }, {
        id: 65,
        profile_image: '/123.jpg',
        name: 'John Sassoon',
        last_appoinment: '2017-10-30 14:45',
        address: "אילת, ישראל",
        phone: '09-0016666'
      }, {
        id: 66,
        profile_image: '/123.jpg',
        name: 'Nechemya Gould',
        last_appoinment: '2017-12-22 11:00',
        address: "ירדן הררי, כרכום, ישראל",
        phone: '05-2582810'
      }, {
        id: 72,
        profile_image: '/123.jpg',
        name: 'Esdras Chicherin',
        last_appoinment: '2017-12-21 16:45',
        address: "כפר יונה, ישראל",
        phone: '057-2910346'
      }, {
        id: 73,
        profile_image: '/123.jpg',
        name: 'Jeriah Luxemburg',
        last_appoinment: '2017-12-11 14:30',
        address: "אילת, ישראל",
        phone: '02-1177952'
      }, {
        id: 78,
        profile_image: '/123.jpg',
        name: 'Ethan Loewe',
        last_appoinment: '2017-11-30 15:15',
        address: "12, ישראל",
        phone: '056-0388404'
      }, {
        id: 82,
        profile_image: '/123.jpg',
        name: 'Oded Schwab',
        last_appoinment: '2017-11-05 16:15',
        address: "512, יפיע, ישראל",
        phone: '0338540115'
      }, {
        id: 86,
        profile_image: '/123.jpg',
        name: 'Moshe Poliakov',
        last_appoinment: '2017-10-24 15:45',
        address: "412, יפיע, ישראל",
        phone: '056-6526165'
      }, {
        id: 99,
        profile_image: '/123.jpg',
        name: 'Shaan Benisch',
        last_appoinment: '2017-10-16 12:30',
        address: "512, יפיע, ישראל",
        phone: '03-9213345'
      }
    ]
  },
  user: {
    permission_level: ['admin', 'senior', 'junior', 'readonly', 'untrusted']
  },
  plugins_list: [
    'custom_groups', 'base', 'multiple_workers', 'colors', 'medical_records'
  ],
  data: {
    boolean: {
      is_address_based: false,
      is_default_user: true,
      is_reminders_set: false,
      isRTL: false
    },
    numerical: {
      listLimit: 20,
      max_proc_shown_without_cat: 5,
      total_price: 10,
      total_duration: 5
    },
    personalEvents: {
      durationDefaultValue: 60
    },
    request_retry_after: 100
  }
}
