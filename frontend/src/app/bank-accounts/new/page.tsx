'use client'

import { useState } from 'react'
import { useRouter } from 'next/navigation'
import Layout from '@/components/Layout'
import api from '@/lib/api'

export default function NewBankAccountPage() {
  const router = useRouter()
  const [name, setName] = useState('')
  const [bankName, setBankName] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      await api.post('/api/v1/bank-accounts', { name, bank_name: bankName })
      router.push('/bank-accounts')
    } catch (err: any) {
      setError(err.response?.data?.detail?.message || '登録に失敗しました')
    } finally {
      setLoading(false)
    }
  }

  return (
    <Layout>
      <div className="max-w-lg">
        <h2 className="text-2xl font-bold text-gray-900 mb-6">銀行口座登録</h2>
        <form onSubmit={handleSubmit} className="space-y-4 bg-white p-6 rounded-xl shadow-sm border">
          {error && <div className="bg-red-50 text-red-600 text-sm p-3 rounded">{error}</div>}
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">口座名</label>
            <input
              required
              value={name}
              onChange={(e) => setName(e.target.value)}
              placeholder="例: 営業部"
              className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">銀行名</label>
            <input
              required
              value={bankName}
              onChange={(e) => setBankName(e.target.value)}
              placeholder="例: 三菱UFJ銀行"
              className="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
          <div className="flex gap-3">
            <button
              type="submit"
              disabled={loading}
              className="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
            >
              {loading ? '登録中...' : '登録'}
            </button>
            <button
              type="button"
              onClick={() => router.back()}
              className="px-6 py-2 bg-white border text-gray-700 rounded-lg hover:bg-gray-50"
            >
              キャンセル
            </button>
          </div>
        </form>
      </div>
    </Layout>
  )
}
