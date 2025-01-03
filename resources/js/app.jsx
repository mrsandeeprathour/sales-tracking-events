import './bootstrap';
import '../css/app.css';
import { createInertiaApp } from '@inertiajs/react'
import { createRoot } from 'react-dom/client'
import '@shopify/polaris/build/esm/styles.css';

import enTranslations from '@shopify/polaris/locales/en.json';
import { AppProvider } from '@shopify/polaris';
// import { Provider} from '@shopify/app-bridge-react';
// import Layout from './Layouts/Layout';
// console.log('Config', Config);

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true })
        return pages[`./Pages/${name}.jsx`]
      },

  setup({ el, App, props }) {
      createRoot(el).render(
          <AppProvider i18n={enTranslations}>
            <App {...props} />
          </AppProvider >
      )
    },
})
