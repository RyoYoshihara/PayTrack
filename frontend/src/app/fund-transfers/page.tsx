'use client'

import { useCallback, useEffect, useState } from 'react'
import Link from 'next/link'
import Layout from '@/components/Layout'
import MonthPicker from '@/components/MonthPicker'
import api from '@/lib/api'
import type { FundTransfer } from '@/types'

const statusLabel: Record<string, { text: string; cls: string }> = {
  scheduled: { text: '予定', cls: 'bg-blue-100 text-blue-800' },
  completed: { text: '完了', cls: 'bg-green-100 text-green-800' },
  cancelled: { text: 'キャンセル', cls: 'bg-gray-100 text-gray-800' },
}

export default function FundTransfersPage() {
  const now = new Date()
  const [year, setYear] = useState(now.getFullYear())
  const [month, setMonth] = useState(now.getMonth() + 1)
  const [transfers, setTransfers] = useState<FundTransfer[]>([])

  const load = useCallback(() => {
    api
      .get('/api/v1/fund-transfers', { params: { year, month } })
      .then((res) => setTransfers(res.data.data))
      .catch(() => {})
  }, [year, month])

  useEffect(() => { load() }, [load])

  const handleConfirm = async (id: string, side: 'from' | 'to') => {
    try {
      await api.patch(`/api/v1/fund-transfers/${id}/confirm`, { side })
      load()
    } catch { /* ignore */ }
  }

  const handleCancel = async (id: string) => {
    if (!confirm('この資金移動をキャンセルしますか？')) return
    try {
      await api.patch(`/api/v1/fund-transfers/${id}/cancel`)
      load()
    } catch { /* ignore */ }
  }

  return (
    <Layout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h2 className="text-2xl font-bold text-gray-900">資金移動</h2>
          <div className="flex items-center gap-4">
            <MonthPicker year={year} month={month} onChange={(y, m) => { setYear(y); setMonth(m) }} />
            <Link
              href="/fund-transfers/new"
              className="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700"
            >
              新規登録
            </Link>
          </div>
        </div>

        <div className="bg-white rounded-xl shadow-sm border overflow-hidden">
          <table className="w-full text-sm">
            <thead className="bg-gray-50 border-b">
              <tr>
                <th className="px-4 py-3 text-left font-medium text-gray-500">予定日</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500">出金元</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500">入金先</th>
                <th className="px-4 py-3 text-right font-medium text-gray-500">金額</th>
                <th className="px-4 py-3 text-center font-medium text-gray-500">出金確認</th>
                <th className="px-4 py-3 text-center font-medium text-gray-500">入金確認</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500">状態</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500">操作</th>
              </tr>
            </thead>
            <tbody className="divide-y">
              {transfers.map((ft) => {
                const st = statusLabel[ft.status] || statusLabel.scheduled
                return (
                  <tr key={ft.id} className="hover:bg-gray-50">
                    <td className="px-4 py-3">{ft.scheduled_date}</td>
                    <td className="px-4 py-3 text-xs">{ft.from_account_name}</td>
                    <td className="px-4 py-3 text-xs">{ft.to_account_name}</td>
                    <td className="px-4 py-3 text-right font-medium">¥{ft.amount.toLocaleString()}</td>
                    <td className="px-4 py-3 text-center">
                      {ft.from_confirmed ? (
                        <span className="text-green-600 font-medium">済</span>
                      ) : ft.status === 'scheduled' ? (
                        <button
                          onClick={() => handleConfirm(ft.id, 'from')}
                          className="px-2 py-1 text-xs bg-orange-100 text-orange-700 rounded hover:bg-orange-200"
                        >
                          確認
                        </button>
                      ) : (
                        <span className="text-gray-400">-</span>
                      )}
                    </td>
                    <td className="px-4 py-3 text-center">
                      {ft.to_confirmed ? (
                        <span className="text-green-600 font-medium">済</span>
                      ) : ft.status === 'scheduled' ? (
                        <button
                          onClick={() => handleConfirm(ft.id, 'to')}
                          className="px-2 py-1 text-xs bg-orange-100 text-orange-700 rounded hover:bg-orange-200"
                        >
                          確認
                        </button>
                      ) : (
                        <span className="text-gray-400">-</span>
                      )}
                    </td>
                    <td className="px-4 py-3">
                      <span className={`inline-block px-2 py-0.5 rounded text-xs font-medium ${st.cls}`}>
                        {st.text}
                      </span>
                    </td>
                    <td className="px-4 py-3">
                      {ft.status === 'scheduled' && (
                        <button
                          onClick={() => handleCancel(ft.id)}
                          className="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                        >
                          取消
                        </button>
                      )}
                    </td>
                  </tr>
                )
              })}
              {transfers.length === 0 && (
                <tr>
                  <td colSpan={8} className="px-4 py-8 text-center text-gray-400">
                    データがありません
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </Layout>
  )
}
