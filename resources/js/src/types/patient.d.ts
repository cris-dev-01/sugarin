export interface Patient {
    id: number;
    document_type: number;
    document: string;
    formatted_document: string;
    illness_found_at: Date;
    initial_max_glucose_value: number;
}
