'use client'

import { useEffect, useState } from 'react'
import Link from 'next/link'
import Layout from '@/components/Layout'
import api from '@/lib/api'
import type { Rule } from '@/types'

export default function RulesPage() {
  const [rules, setRules] = useState<Rule[]>([])

  const load = () => {
    api
      .get('/api/v1/rules', { params: { per_page: 100 } })
      .then((res) => setRules(res.data.data))
      .catch(() => {})
  }

  useEffect(() => { load() }, [])

  const handleDelete = async (id: string) => {
    if (!confirm('このルールを削除しますか？')) return
    try {
      await api.delete(`/api/v1/rules/${id}`)
      load()
    } catch {
      // ignore
    }
  }

  return (
    <Layout>
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <h2 className="text-2xl font-bold text-gray-900">ルール設定</h2>
          <Link
            href="/rules/new"
            className="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700"
          >
            新規登録
          </Link>
        </div>
        <div className="bg-white rounded-xl shadow-sm border overflow-hidden">
          <table className="w-full text-sm">
            <thead className="bg-gray-50 border-b">
              <tr>
                <th className="px-4 py-3 text-left font-medium text-gray-500">件名</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500">種別</th>
                <th className="px-4 py-3 text-right font-medium text-gray-500">金額</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500">繰り返し</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500">日</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500">期間</th>
                <th className="px-4 py-3 text-left font-medium text-gray-500">操作</th>
              </tr>
            </thead>
            <tbody className="divide-y">
              {rules.map((rule) => (
                <tr key={rule.id} className="hover:bg-gray-50">
                  <td className="px-4 py-3">{rule.title}</td>
                  <td className="px-4 py-3">
                    <span className={rule.type === 'income' ? 'text-green-600' : 'text-red-600'}>
                      {rule.type === 'income' ? '入金' : '支払'}
                    </span>
                  </td>
                  <td className="px-4 py-3 text-right font-medium">
                    ¥{rule.amount.toLocaleString()}
                  </td>
                  <td className="px-4 py-3">
                    {rule.recurrence === 'monthly' ? '毎月' : '単発'}
                  </td>
                  <td className="px-4 py-3">
                    {rule.day_of_month ? `${rule.day_of_month}日` : '-'}
                  </td>
                  <td className="px-4 py-3 text-xs text-gray-500">
                    {rule.start_month || '?'} 〜 {rule.end_month || '無期限'}
                  </td>
                  <td className="px-4 py-3">
                    <button
                      onClick={() => handleDelete(rule.id)}
                      className="px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200"
                    >
                      削除
                    </button>
                  </td>
                </tr>
              ))}
              {rules.length === 0 && (
                <tr>
                  <td colSpan={7} className="px-4 py-8 text-center text-gray-400">
                    ルールがありません
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
