export interface User {
    id: number;
    name: string;
    email: string;
    patient: Patient;
    permissions: string[];
    role: string;
    created_at: Date;
}
