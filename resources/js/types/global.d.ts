import { route as ziggyRoute } from 'ziggy-js';
import Echo from 'laravel-echo';

declare global {
  var route: typeof ziggyRoute;
  interface Window {
    Echo: Echo;
    Pusher: any;
    axios: any;
    Ziggy: {
      url: string;
      port: number | null;
      defaults: Record<string, any>;
      routes: Record<string, {
        uri: string;
        methods: string[];
        parameters?: string[];
        wheres?: Record<string, string>;
      }>;
    };
  }
}

export {};
