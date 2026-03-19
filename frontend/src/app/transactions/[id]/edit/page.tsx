'use client'

import { useEffect, useState } from 'react'
import { useParams, useRouter } from 'next/navigation'
import Layout from '@/components/Layout'
import api from '@/lib/api'
import type { Transaction } from '@/types'

export default function EditTransactionPage() {
  const router = useRouter()
  const params = useParams()
  const id = params.id as string

  const [title, setTitle] = useState('')
  const [amount, setAmount] = useState('')
  const [scheduledDate, setScheduledDate] = useState('')
  const [memo, setMemo] = useState('')
  const [original, setOriginal] = useState<Transaction | null>(null)
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    api
      .get(`/api/v1/transactions/${id}`)
      .then((res) => {
        const txn: Transaction = res.data.data
        setOriginal(txn)
        setTitle(txn.title)
        setAmount(String(txn.amount))
        setScheduledDate(txn.scheduled_date)
        setMemo(txn.memo || '')
      })
      .catch(() => {
        setError('トランザクションの取得に失敗しました')
      })
  }, [id])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      await api.put(`/api/v1/transactions/${id}`, {
        title,
        amount: Number(amount),
        scheduled_date: scheduledDate,
        memo: memo || null,
      })
      router.push('/transactions')
    } catch (err: any) {
      setError(err.response?.data?.detail?.message || '更新に失敗しました')
    } finally {
      setLoading(false)
    }
  }

  if (!original && !error) {
    return <Layout><div className="text-gray-400">読み込み中...</div></Layout>
  }

  return (
    <Layout>
      <div className="max-w-lg">
        <h2 className="text-2xl font-bold text-gray-900 mb-6">入出金編集</h2>
        <form onSubmit={handleSubmit} className="space-y-4 bg-white p-6 rounded-xl shadow-sm border">
          {error && <div className="bg-red-50 text-red-600 text-sm p-3 rounded">{error}</div>}
          {original && (original.status === 'completed' || original.status === 'cancelled') && (
            <div className="bg-yellow-50 text-yellow-700 text-sm p-3 rounded">
              完了・キャンセル済みのトランザクションは編集できません
            </div>
          )}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">件名</label>
            <input
              required
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              disabled={original?.status === 'completed' || original?.status === 'cancelled'}
              className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-100"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">金額</label>
            <input
              type="number"
              required
              min="1"
              value={amount}
              onChange={(e) => setAmount(e.target.value)}
              disabled={original?.status === 'completed' || original?.status === 'cancelled'}
              className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-100"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">種別</label>
            <input
              value={original?.type === 'income' ? '入金' : '支払'}
              disabled
              className="w-full px-3 py-2 border rounded-lg bg-gray-100"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">予定日</label>
            <input
              type="date"
              required
              value={scheduledDate}
              onChange={(e) => setScheduledDate(e.target.value)}
              disabled={original?.status === 'completed' || original?.status === 'cancelled'}
              className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-100"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">メモ</label>
            <textarea
              value={memo}
              onChange={(e) => setMemo(e.target.value)}
              rows={3}
              disabled={original?.status === 'completed' || original?.status === 'cancelled'}
              className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-100"
            />
          </div>
          <div className="flex gap-3">
            {original && original.status !== 'completed' && original.status !== 'cancelled' && (
              <button
                type="submit"
                disabled={loading}
                className="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
              >
                {loading ? '更新中...' : '更新'}
              </button>
            )}
            <button
              type="button"
              onClick={() => router.back()}
              className="px-6 py-2 bg-white border text-gray-700 rounded-lg hover:bg-gray-50"
            >
              戻る
            </button>
          </div>
        </form>
      </div>
    </Layout>
  )
}
