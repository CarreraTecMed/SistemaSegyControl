// /** @type {import('tailwindcss').Config} */
// export default {
//   content: [
//     "./index.html",
//     "./src/**/*.{js,jsx}",
    
//   ],
//   theme: {

//     extend: {},
//   },
//   plugins: [
//     require('@codaworks/react-glow/tailwind')
//   ],
  
// }

// import type { Config } from 'tailwindcss';

export default {
    content: [
        "./resources/views/app.blade.php",
        "./resources/app/**/*.{js,ts,jsx,tsx}",
    ],
    theme: {
        extend: {
            zIndex: {
                1000: "1000",
                2000: "2000",
                5000: "5000",
                5050: "5050",
            },
        },
    },
    plugins: [],
};

