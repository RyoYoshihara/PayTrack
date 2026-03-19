const statusConfig: Record<string, { label: string; className: string }> = {
  scheduled: { label: '予定', className: 'bg-blue-100 text-blue-800' },
  completed: { label: '完了', className: 'bg-green-100 text-green-800' },
  carried_over: { label: '繰越', className: 'bg-yellow-100 text-yellow-800' },
  cancelled: { label: 'キャンセル', className: 'bg-gray-100 text-gray-800' },
}

export default function StatusBadge({ status }: { status: string }) {
  const config = statusConfig[status] || { label: status, className: 'bg-gray-100 text-gray-800' }
  return (
    <span className={`inline-block px-2 py-0.5 rounded text-xs font-medium ${config.className}`}>
      {config.label}
    </span>
  )
}
