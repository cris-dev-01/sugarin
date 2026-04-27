export interface User {
    id: number;
    name: string;
    email: string;
    patient: Patient;
    created_at: Date;
}
