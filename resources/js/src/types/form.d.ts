export interface FormGlucoseRanges {
    min_fasting_value: number | null;
    max_fasting_value: number | null;
    min_non_fasting_value: number | null;
    max_non_fasting_value: number | null;
    alias: string | null;
}

export interface FormPatients {
    glucose_range_id: number;
    name: string;
    email: string;
    document: string;
    illness_found_at: string;
    initial_max_glucose_value: number;
}