/// <reference types="vite/client" />
declare module 'qrcode' {
    const QRCode: {
        toDataURL(text: string): Promise<string>;
        toString(text: string): Promise<string>;
    };
    export default QRCode;
}
declare module '*.vue' {
    import type { DefineComponent } from 'vue';
    const component: DefineComponent<object, object, unknown>;
    export default component;
}
