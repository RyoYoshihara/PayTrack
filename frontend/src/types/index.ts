export interface User {
  id: string
  email: string
  created_at: string
}

export interface TokenResponse {
  access_token: string
  refresh_token: string
  token_type: string
  expires_in: number
}

export interface BankAccount {
  id: string
  name: string
  bank_name: string
  sort_order: number
  created_at: string
  updated_at: string
}

export interface Rule {
  id: string
  user_id: string
  bank_account_id: string | null
  title: string
  amount: number
  type: 'income' | 'expense'
  recurrence: 'once' | 'monthly'
  day_of_month: number | null
  start_month: string | null
  end_month: string | null
  memo: string | null
  created_at: string
  updated_at: string
}

export interface Transaction {
  id: string
  rule_id: string | null
  bank_account_id: string | null
  title: string
  amount: number
  type: 'income' | 'expense'
  scheduled_date: string
  actual_date: string | null
  status: 'scheduled' | 'completed' | 'carried_over' | 'cancelled'
  carried_over_from: string | null
  memo: string | null
  created_at: string
  updated_at: string
}

export interface FundTransfer {
  id: string
  from_account_id: string
  to_account_id: string
  from_account_name: string | null
  to_account_name: string | null
  amount: number
  scheduled_date: string
  memo: string | null
  from_confirmed: boolean
  to_confirmed: boolean
  status: 'scheduled' | 'completed' | 'cancelled'
  created_at: string
  updated_at: string
}

export interface DashboardSummary {
  year: number
  month: number
  total_income: number
  total_expense: number
  balance: number
  status_summary: {
    scheduled: number
    completed: number
    carried_over: number
    cancelled: number
  }
}

export interface AccountSummary {
  account_id: string
  account_name: string
  total_income: number
  total_expense: number
  balance: number
}

export interface PaginatedResponse<T> {
  data: T[]
  total: number
  page: number
  per_page: number
}
