'use client'

import Link from 'next/link'
import { usePathname, useRouter } from 'next/navigation'
import { clearTokens, getRefreshToken } from '@/lib/auth'
import api from '@/lib/api'

const navItems = [
  { href: '/dashboard', label: 'ダッシュボード' },
  { href: '/schedule', label: '入出金予定' },
  { href: '/transactions', label: '入出金一覧' },
  { href: '/fund-transfers', label: '資金移動' },
  { href: '/rules', label: 'ルール設定' },
  { href: '/bank-accounts', label: '銀行口座管理' },
]

export default function Sidebar() {
  const pathname = usePathname()
  const router = useRouter()

  const handleLogout = async () => {
    const refreshToken = getRefreshToken()
    try {
      if (refreshToken) {
        await api.post('/api/v1/auth/logout', { refresh_token: refreshToken })
      }
    } catch {
      // ignore
    }
    clearTokens()
    router.push('/login')
  }

  return (
    <aside className="w-64 bg-white shadow-md min-h-screen flex flex-col">
      <div className="p-6 border-b">
        <h1 className="text-xl font-bold text-indigo-600">PayTrack</h1>
      </div>
      <nav className="flex-1 p-4 space-y-1">
        {navItems.map((item) => (
          <Link
            key={item.href}
            href={item.href}
            className={`block px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
              pathname.startsWith(item.href)
                ? 'bg-indigo-50 text-indigo-700'
                : 'text-gray-600 hover:bg-gray-50'
            }`}
          >
            {item.label}
          </Link>
        ))}
      </nav>
      <div className="p-4 border-t">
        <button
          onClick={handleLogout}
          className="w-full px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg text-left"
        >
          ログアウト
        </button>
      </div>
    </aside>
  )
}
