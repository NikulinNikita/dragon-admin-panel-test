export interface Notification {
    title: string | null;
    message: string;
    type: string;
}

export interface CoreState {
    notification: Notification | null,
    systemNotification: Notification | null
}