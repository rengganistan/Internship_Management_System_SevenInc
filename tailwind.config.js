module.exports = {
    content: [
      "./resources/**/*.blade.php",
      "./resources/**/*.js",
      "./resources/**/*.vue",
      "./node_modules/flowbite/**/*.js"
    ],
    theme: {
      extend: {
        colors: {
          primary: {
            DEFAULT: '#10B981',
            50:  '#ECFDF5',
            100: '#D1FAE5',
            150: '#B8F7D5',
            200: '#A7F3D0',
            250: '#90EFC1',
            300: '#6EE7B7',
            350: '#4DDFAB',
            400: '#34D399',
            450: '#2DC78F',
            500: '#10B981',
            550: '#0FA06F',
            600: '#059669',
            650: '#048A60',
            700: '#047857',
            750: '#03694C',
            800: '#065F46',
            850: '#054D39',
            900: '#064E3B',
            950: '#032F22'
          },
          admin: {
            primary: '#2D8659',
            'primary-dark': '#1F5F3F',
            secondary: '#E8F5E9',
            accent: '#FF6B35',
            'text-dark': '#1B3A34',
            'text-mid': '#4B5F5A',
            success: '#388E3C',
            warning: '#FBC02D',
            error: '#D32F2F',
            border: '#DCE7E1',
            bg: '#F4F8F6',
          },
        },
      },
    },
    plugins: [
        require('flowbite/plugin')
    ],
  }

