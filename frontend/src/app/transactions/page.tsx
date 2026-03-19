'use client'

import { useEffect, useState } from 'react'
import Link from 'next/link'
import Layout from '@/components/Layout'
import MonthPicker from '@/components/MonthPicker'
import StatusBadge from '@/components/StatusBadge'
import api from '@/lib/api'
import type { Transaction } from '@/types'

export default function TransactionsPage() {
  const now = new Date()
  const [year, setYear] = useState(now.getFullYear())
  const [month, setMonth] = useState(now.getMonth() + 1)
  const [transactions, setTransactions] = useState<Transaction[]>([])
  const [typeFilter, setTypeFilter] = useState('')
  const [statusFilter, setStatusFilter] = useState('')

  const load = () => {
    const params: Record<string, any> = { year, month, per_page: 100 }
    if (typeFilter) params.type = typeFilter
    if (statusFilter) params.status = statusFilter
    api
      .get('/api/v1/transactions', { params })
      .then((res) => setTransactions(res.data.data))
      .catch(() => {})
  }

  useEffect(() => { load() }, [year, month, typeFilter, statusFilter])

  const updateStatus = async (id: string, status: string) => {
    const body: any = { status }
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

  return (
    <Layout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h2 className="text-2xl font-bold text-gray-900">入出金一覧</h2>
          <div className="flex items-center gap-4">
            <MonthPicker year={year} month={month} onChange={(y, m) => { setYear(y); setMonth(m) }} />
            <Link
              href="/transactions/new"
              className="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700"
            >
              新規登録
            </Link>
          </div>
        </div>

        <div className="flex gap-4">
          <select
            value={typeFilter}
            onChange={(e) => setTypeFilter(e.target.value)}
            className="px-3 py-1.5 border rounded-lg text-sm"
          >
            <option value="">全種別</option>
            <option value="income">入金</option>
            <option value="expense">支払</option>
          </select>
          <select
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
            className="px-3 py-1.5 border rounded-lg text-sm"
          >
            <option value="">全ステータス</option>
            <option value="scheduled">予定</option>
            <option value="completed">完了</option>
            <option value="carried_over">繰越</option>
            <option value="cancelled">キャンセル</option>
          </select>
        </div>

        <div className="bg-white rounded-xl shadow-sm border overflow-hidden">
          <table className="w-full text-sm">
            <thead className="bg-gray-50 border-b">
              <tr>
                <th className="px-4 py-3 text-left font-medium text-gray-500">件名</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500">種別</th>
                <th className="px-4 py-3 text-right font-medium text-gray-500">金額</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500">予定日</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500">実施日</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500">ステータス</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500">操作</th>
              </tr>
            </thead>
            <tbody className="divide-y">
              {transactions.map((txn) => (
                <tr key={txn.id} className="hover:bg-gray-50">
                  <td className="px-4 py-3">{txn.title}</td>
                  <td className="px-4 py-3">
                    <span className={txn.type === 'income' ? 'text-green-600' : 'text-red-600'}>
                      {txn.type === 'income' ? '入金' : '支払'}
                    </span>
                  </td>
                  <td className="px-4 py-3 text-right font-medium">
                    ¥{txn.amount.toLocaleString()}
                  </td>
                  <td className="px-4 py-3">{txn.scheduled_date}</td>
                  <td className="px-4 py-3">{txn.actual_date || '-'}</td>
                  <td className="px-4 py-3"><StatusBadge status={txn.status} /></td>
                  <td className="px-4 py-3">
                    <div className="flex gap-1">
                      {(txn.status === 'scheduled' || txn.status === 'carried_over') && (
                        <>
                          <Link
                            href={`/transactions/${txn.id}/edit`}
                            className="px-2 py-1 text-xs bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200"
                          >
                            編集
                          </Link>
                          <button
                            onClick={() => updateStatus(txn.id, 'completed')}
                            className="px-2 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200"
                          >
                            完了
                          </button>
                          <button
                            onClick={() => updateStatus(txn.id, 'carried_over')}
                            className="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200"
                          >
                            繰越
                          </button>
                          <button
                            onClick={() => updateStatus(txn.id, 'cancelled')}
                            className="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                          >
                            取消
                          </button>
                        </>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
              {transactions.length === 0 && (
                <tr>
                  <td colSpan={7} className="px-4 py-8 text-center text-gray-400">
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
