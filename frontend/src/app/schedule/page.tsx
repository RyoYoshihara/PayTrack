'use client'

import { useCallback, useEffect, useState } from 'react'
import Layout from '@/components/Layout'
import MonthPicker from '@/components/MonthPicker'
import api from '@/lib/api'
import type { Transaction } from '@/types'

function ScheduleTable({
  title,
  color,
  transactions,
  total,
  onStatusChange,
}: {
  title: string
  color: 'green' | 'red'
  transactions: Transaction[]
  total: number
  onStatusChange: (id: string, status: string) => void
}) {
  const colorMap = {
    green: { header: 'bg-green-50', text: 'text-green-700', total: 'text-green-600' },
    red: { header: 'bg-red-50', text: 'text-red-700', total: 'text-red-600' },
  }
  const c = colorMap[color]

  return (
    <div className="bg-white rounded-xl shadow-sm border overflow-hidden">
      <div className={`px-4 py-3 ${c.header} border-b flex items-center justify-between`}>
        <h3 className={`font-semibold ${c.text}`}>{title}</h3>
        <span className={`text-lg font-bold ${c.total}`}>
          合計: ¥{total.toLocaleString()}
        </span>
      </div>
      <table className="w-full text-sm">
        <thead className="bg-gray-50 border-b">
          <tr>
            <th className="px-4 py-3 text-left font-medium text-gray-500">件名</th>
            <th className="px-4 py-3 text-right font-medium text-gray-500">金額</th>
            <th className="px-4 py-3 text-left font-medium text-gray-500">予定日</th>
            <th className="px-4 py-3 text-left font-medium text-gray-500">メモ</th>
            <th className="px-4 py-3 text-left font-medium text-gray-500">操作</th>
          </tr>
        </thead>
        <tbody className="divide-y">
          {transactions.map((txn) => (
            <tr key={txn.id} className="hover:bg-gray-50">
              <td className="px-4 py-3">{txn.title}</td>
              <td className="px-4 py-3 text-right font-medium">
                ¥{txn.amount.toLocaleString()}
              </td>
              <td className="px-4 py-3">{txn.scheduled_date}</td>
              <td className="px-4 py-3 text-gray-500 text-xs truncate max-w-[200px]">
                {txn.memo || '-'}
              </td>
              <td className="px-4 py-3">
                <div className="flex gap-1">
                  <button
                    onClick={() => onStatusChange(txn.id, 'completed')}
                    className="px-2 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200"
                  >
                    完了
                  </button>
                  <button
                    onClick={() => onStatusChange(txn.id, 'carried_over')}
                    className="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200"
                  >
                    繰越
                  </button>
                  <button
                    onClick={() => onStatusChange(txn.id, 'cancelled')}
                    className="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                  >
                    取消
                  </button>
                </div>
              </td>
            </tr>
          ))}
          {transactions.length === 0 && (
            <tr>
              <td colSpan={5} className="px-4 py-8 text-center text-gray-400">
                予定はありません
              </td>
            </tr>
          )}
        </tbody>
      </table>
    </div>
  )
}

export default function SchedulePage() {
  const now = new Date()
  const [year, setYear] = useState(now.getFullYear())
  const [month, setMonth] = useState(now.getMonth() + 1)
  const [incomes, setIncomes] = useState<Transaction[]>([])
  const [expenses, setExpenses] = useState<Transaction[]>([])

  const load = useCallback(() => {
    const base = { year, month, status: 'scheduled', per_page: 100 }
    api
      .get('/api/v1/transactions', { params: { ...base, type: 'income' } })
      .then((res) => setIncomes(res.data.data))
      .catch(() => {})
    api
      .get('/api/v1/transactions', { params: { ...base, type: 'expense' } })
      .then((res) => setExpenses(res.data.data))
      .catch(() => {})
  }, [year, month])

  useEffect(() => { load() }, [load])

  const handleStatusChange = async (id: string, status: string) => {
    const body: Record<string, string> = { status }
    if (status === 'completed') {
      body.actual_date = new Date().toISOString().split('T')[0]
    }
    try {
      await api.patch(`/api/v1/transactions/${id}/status`, body)
      load()
    } catch {
      // ignore
    }
  }

  const incomeTotal = incomes.reduce((sum, t) => sum + t.amount, 0)
  const expenseTotal = expenses.reduce((sum, t) => sum + t.amount, 0)

  return (
    <Layout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h2 className="text-2xl font-bold text-gray-900">入出金予定</h2>
          <MonthPicker year={year} month={month} onChange={(y, m) => { setYear(y); setMonth(m) }} />
        </div>

        <ScheduleTable
          title="入金予定"
          color="green"
          transactions={incomes}
          total={incomeTotal}
          onStatusChange={handleStatusChange}
        />

        <ScheduleTable
          title="支払予定"
          color="red"
          transactions={expenses}
          total={expenseTotal}
          onStatusChange={handleStatusChange}
        />
      </div>
    </Layout>
  )
}
