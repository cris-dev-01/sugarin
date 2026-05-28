export interface User {
    id: number;
    name: string;
    email: string;
    patient: Patient;
    permissions: string[];
    created_at: Date;
}
