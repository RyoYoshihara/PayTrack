'use client'

import { useCallback, useEffect, useState } from 'react'
import Link from 'next/link'
import Layout from '@/components/Layout'
import api from '@/lib/api'
import type { BankAccount } from '@/types'

export default function BankAccountsPage() {
  const [accounts, setAccounts] = useState<BankAccount[]>([])

  const load = useCallback(() => {
    api.get('/api/v1/bank-accounts').then((res) => setAccounts(res.data.data)).catch(() => {})
  }, [])

  useEffect(() => { load() }, [load])

  const handleDelete = async (id: string) => {
    if (!confirm('この口座を削除しますか？')) return
    try {
      await api.delete(`/api/v1/bank-accounts/${id}`)
      load()
    } catch { /* ignore */ }
  }

  const moveUp = async (index: number) => {
    if (index === 0) return
    const ids = accounts.map((a) => a.id)
    ;[ids[index - 1], ids[index]] = [ids[index], ids[index - 1]]
    try {
      const res = await api.put('/api/v1/bank-accounts/reorder', { ids })
      setAccounts(res.data.data)
    } catch { /* ignore */ }
  }

  const moveDown = async (index: number) => {
    if (index === accounts.length - 1) return
    const ids = accounts.map((a) => a.id)
    ;[ids[index], ids[index + 1]] = [ids[index + 1], ids[index]]
    try {
      const res = await api.put('/api/v1/bank-accounts/reorder', { ids })
      setAccounts(res.data.data)
    } catch { /* ignore */ }
  }

  return (
    <Layout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h2 className="text-2xl font-bold text-gray-900">銀行口座管理</h2>
          <Link
            href="/bank-accounts/new"
            className="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700"
          >
            新規登録
          </Link>
        </div>
        <div className="bg-white rounded-xl shadow-sm border overflow-hidden">
          <table className="w-full text-sm">
            <thead className="bg-gray-50 border-b">
              <tr>
                <th className="px-4 py-3 text-left font-medium text-gray-500">順番</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500">口座名</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500">銀行名</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500">操作</th>
              </tr>
            </thead>
            <tbody className="divide-y">
              {accounts.map((account, i) => (
                <tr key={account.id} className="hover:bg-gray-50">
                  <td className="px-4 py-3">
                    <div className="flex gap-1">
                      <button
                        onClick={() => moveUp(i)}
                        disabled={i === 0}
                        className="px-2 py-1 text-xs bg-gray-100 rounded hover:bg-gray-200 disabled:opacity-30"
                      >
                        ▲
                      </button>
                      <button
                        onClick={() => moveDown(i)}
                        disabled={i === accounts.length - 1}
                        className="px-2 py-1 text-xs bg-gray-100 rounded hover:bg-gray-200 disabled:opacity-30"
                      >
                        ▼
                      </button>
                    </div>
                  </td>
                  <td className="px-4 py-3 font-medium">{account.name}</td>
                  <td className="px-4 py-3">{account.bank_name}</td>
                  <td className="px-4 py-3">
                    <button
                      onClick={() => handleDelete(account.id)}
                      className="px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200"
                    >
                      削除
                    </button>
                  </td>
                </tr>
              ))}
              {accounts.length === 0 && (
                <tr>
                  <td colSpan={4} className="px-4 py-8 text-center text-gray-400">
                    口座が登録されていません
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
