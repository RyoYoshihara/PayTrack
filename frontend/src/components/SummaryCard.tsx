interface Props {
  title: string
  value: string
  color?: string
}

export default function SummaryCard({ title, value, color = 'text-gray-900' }: Props) {
  return (
    <div className="bg-white rounded-xl shadow-sm p-6 border">
      <p className="text-sm text-gray-500 mb-1">{title}</p>
      <p className={`text-2xl font-bold ${color}`}>{value}</p>
    </div>
  )
}
