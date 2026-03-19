'use client'

import { useEffect, useState } from 'react'
import Layout from '@/components/Layout'
import MonthPicker from '@/components/MonthPicker'
import SummaryCard from '@/components/SummaryCard'
import api from '@/lib/api'
import type { AccountSummary, DashboardSummary } from '@/types'

function formatCurrency(n: number) {
  return `¥${n.toLocaleString()}`
}

export default function DashboardPage() {
  const now = new Date()
  const [year, setYear] = useState(now.getFullYear())
  const [month, setMonth] = useState(now.getMonth() + 1)
  const [summary, setSummary] = useState<DashboardSummary | null>(null)
  const [accountSummaries, setAccountSummaries] = useState<AccountSummary[]>([])
  const [generating, setGenerating] = useState(false)
  const [generateResult, setGenerateResult] = useState<string | null>(null)

  const loadSummary = () => {
    api
      .get('/api/v1/dashboard/summary', { params: { year, month } })
      .then((res) => setSummary(res.data.data))
      .catch(() => {})
    api
      .get('/api/v1/dashboard/summary-by-account', { params: { year, month } })
      .then((res) => setAccountSummaries(res.data.data))
      .catch(() => {})
  }

  useEffect(() => {
    setGenerateResult(null)
    loadSummary()
  }, [year, month])

  const handleGenerate = async () => {
    setGenerating(true)
    setGenerateResult(null)
    try {
      const target = `${year}-${String(month).padStart(2, '0')}`
      const res = await api.post('/api/v1/batch/generate', { target_month: target })
      const { generated_count, skipped_count } = res.data.data
      setGenerateResult(
        `${generated_count}件生成、${skipped_count}件スキップしました`
      )
      loadSummary()
    } catch {
      setGenerateResult('生成に失敗しました')
    } finally {
      setGenerating(false)
    }
  }

  return (
    <Layout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h2 className="text-2xl font-bold text-gray-900">ダッシュボード</h2>
          <MonthPicker year={year} month={month} onChange={(y, m) => { setYear(y); setMonth(m) }} />
        </div>
        {summary && (
          <>
            {accountSummaries.length > 0 && (
              <div className="space-y-3">
                <h3 className="text-lg font-semibold text-gray-900">口座別サマリー（確定）</h3>
                {accountSummaries.map((as) => (
                  <div key={as.account_id} className="bg-white rounded-xl shadow-sm p-4 border">
                    <p className="font-medium text-gray-700 mb-3">{as.account_name}</p>
                    <div className="grid grid-cols-3 gap-4">
                      <div>
                        <p className="text-xs text-gray-500">入金合計</p>
                        <p className="text-lg font-bold text-green-600">{formatCurrency(as.total_income)}</p>
                      </div>
                      <div>
                        <p className="text-xs text-gray-500">支払合計</p>
                        <p className="text-lg font-bold text-red-600">{formatCurrency(as.total_expense)}</p>
                      </div>
                      <div>
                        <p className="text-xs text-gray-500">差引残高</p>
                        <p className={`text-lg font-bold ${as.balance >= 0 ? 'text-indigo-600' : 'text-red-600'}`}>
                          {formatCurrency(as.balance)}
                        </p>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}

            <div className="bg-white rounded-xl shadow-sm p-6 border">
              <h3 className="text-lg font-semibold mb-4">ステータス別件数</h3>
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div className="text-center">
                  <p className="text-2xl font-bold text-blue-600">{summary.status_summary.scheduled}</p>
                  <p className="text-sm text-gray-500">予定</p>
                </div>
                <div className="text-center">
                  <p className="text-2xl font-bold text-green-600">{summary.status_summary.completed}</p>
                  <p className="text-sm text-gray-500">完了</p>
                </div>
                <div className="text-center">
                  <p className="text-2xl font-bold text-yellow-600">{summary.status_summary.carried_over}</p>
                  <p className="text-sm text-gray-500">繰越</p>
                </div>
                <div className="text-center">
                  <p className="text-2xl font-bold text-gray-600">{summary.status_summary.cancelled}</p>
                  <p className="text-sm text-gray-500">キャンセル</p>
                </div>
              </div>
            </div>

            <div className="bg-white rounded-xl shadow-sm p-6 border">
              <div className="flex items-center justify-between">
                <div>
                  <h3 className="text-lg font-semibold">月次データ生成</h3>
                  <p className="text-sm text-gray-500 mt-1">
                    {year}年{month}月のルールからトランザクションを生成します
                  </p>
                </div>
                <button
                  onClick={handleGenerate}
                  disabled={generating}
                  className="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                >
                  {generating ? '生成中...' : '生成する'}
                </button>
              </div>
              {generateResult && (
                <p className="mt-3 text-sm text-indigo-600 bg-indigo-50 p-3 rounded">
                  {generateResult}
                  <span className="block text-xs text-gray-500 mt-1">
                    ※既に生成済みのデータは自動でスキップされます
                  </span>
                </p>
              )}
            </div>
          </>
        )}
      </div>
    </Layout>
  )
}
