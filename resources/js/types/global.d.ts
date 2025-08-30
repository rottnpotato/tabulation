import { route as ziggyRoute } from 'ziggy-js';
import Echo from 'laravel-echo';

declare global {
  var route: typeof ziggyRoute;
  interface Window {
    Echo: Echo;
    Pusher: any;
    axios: any;
  }
}

export {};
