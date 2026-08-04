export interface GlucoseLogPatient {
    id: number;
    name: string;
}

export interface GlucoseLogStatus {
    id: number;
    name: string;
}

export interface GlucoseLogRange {
    min: number;
    max: number;
}

export interface GlucoseLog {
    id: number;
    value: number;
    time_block: 'mañana' | 'anochecer';
    user_patient: GlucoseLogPatient;
    status: GlucoseLogStatus;
    is_abnormal: boolean;
    range?: GlucoseLogRange;
    created_at: string;
}
