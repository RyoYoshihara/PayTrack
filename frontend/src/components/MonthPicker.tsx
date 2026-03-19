'use client'

interface Props {
  year: number
  month: number
  onChange: (year: number, month: number) => void
}

export default function MonthPicker({ year, month, onChange }: Props) {
  const prev = () => {
    if (month === 1) onChange(year - 1, 12)
    else onChange(year, month - 1)
  }
  const next = () => {
    if (month === 12) onChange(year + 1, 1)
    else onChange(year, month + 1)
  }

  return (
    <div className="flex items-center gap-4">
      <button onClick={prev} className="px-3 py-1 bg-white border rounded hover:bg-gray-50">
        &lt;
      </button>
      <span className="text-lg font-semibold min-w-[120px] text-center">
        {year}年{month}月
      </span>
      <button onClick={next} className="px-3 py-1 bg-white border rounded hover:bg-gray-50">
        &gt;
      </button>
    </div>
  )
}
