import './bootstrap';
import '../css/app.css';
import { createInertiaApp } from '@inertiajs/react'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client'
import '@shopify/polaris/build/esm/styles.css';

import enTranslations from '@shopify/polaris/locales/en.json';
import { AppProvider } from '@shopify/polaris';


import {   Frame  } from '@shopify/polaris';


const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
      resolvePageComponent(
          `./Pages/${name}.jsx`,
          import.meta.glob('./Pages/**/*.jsx'),
      ),
  setup({ el, App, props }) {
      const root = createRoot(el);

      root.render(<AppProvider ii18n={enTranslations} >
        <Frame>
            <App {...props} />
        </Frame>
      </AppProvider>);
  },
  progress: {
      color: '#4B5563',
  },
});

