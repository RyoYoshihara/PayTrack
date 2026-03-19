import type { Metadata } from 'next'
import './globals.css'

export const metadata: Metadata = {
  title: 'PayTrack',
  description: '入出金管理システム',
}

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="ja">
      <body className="bg-gray-50 min-h-screen">{children}</body>
    </html>
  )
}
