export interface TriagePatientRisk {
    id: number;
    name: string;
    has_low_recent: boolean;
    hours_since_last_log: number | null;
    in_range_percentage: number | null;
}

export interface TriageOverview {
    low_recent_count: number;
    inactive_count: number;
    good_control_percentage: number;
    recent_event_window_hours: number;
    good_control_threshold_percentage: number;
    patients: TriagePatientRisk[];
}

export interface PatientSummaryEventsCount {
    total: number;
    'mañana': number;
    'anochecer': number;
}

export interface PatientSummaryValueStats {
    avg: number | null;
    stddev: number | null;
}

export interface PatientSummaryLastReading {
    value: number;
    time_block: 'mañana' | 'anochecer';
    status: string;
    created_at: string;
}

export interface PatientSummaryStatusDistributionItem {
    status: string;
    count: number;
    percentage: number;
}

export interface PatientSummaryRecentLog {
    id: number;
    value: number;
    time_block: 'mañana' | 'anochecer';
    status: string;
    created_at: string;
}

export interface PaginatedPatientLogs {
    data: PatientSummaryRecentLog[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export interface PatientSummaryInRangePercentage {
    combined: number | null;
    'mañana': number | null;
    'anochecer': number | null;
}

export interface PatientSummary {
    patient: {
        id: number;
        name: string;
        email: string;
        formatted_document: string;
    };
    period_days: number;
    in_range_percentage: PatientSummaryInRangePercentage;
    events: {
        bajo: PatientSummaryEventsCount;
        elevado: PatientSummaryEventsCount;
    };
    value_stats: {
        'mañana': PatientSummaryValueStats;
        'anochecer': PatientSummaryValueStats;
    };
    streak_days: number;
    adherence_percentage: number;
    last_reading: PatientSummaryLastReading | null;
    status_distribution: PatientSummaryStatusDistributionItem[];
    recent_logs: PatientSummaryRecentLog[];
}

export type SummaryPeriod = 7 | 30 | 90;

export type TriageCriteria = 'low_recent' | 'inactive' | 'good_control';

export interface TriagePatientLowRecent {
    id: number;
    name: string;
    last_low_value: number;
    last_low_time_block: 'mañana' | 'anochecer';
    last_low_at: string;
}

export interface TriagePatientInactive {
    id: number;
    name: string;
    hours_since_last_log: number | null;
    last_log_at: string | null;
}

export interface TriagePatientGoodControl {
    id: number;
    name: string;
    in_range_percentage: number;
}

export interface TriagePatientsByCriteria {
    criteria: TriageCriteria;
    patients: TriagePatientLowRecent[] | TriagePatientInactive[] | TriagePatientGoodControl[];
}
