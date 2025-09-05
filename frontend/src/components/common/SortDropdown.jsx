import { Listbox } from '@headlessui/react'
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/react/20/solid'
import { useTranslation } from 'react-i18next'

export default function SortDropdown({ sort, setSort }) {
  const { t } = useTranslation()
  const options = [
    { value: 'newest', label: t('sortNewest') },
    { value: 'oldest', label: t('sortOldest') },
    { value: 'name', label: t('sortName') },
  ]

  return (
    <Listbox value={sort} onChange={setSort}>
      <div className="relative w-48">
        <Listbox.Button className="relative w-full cursor-pointer rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 py-2 pl-3 pr-10 text-left focus:outline-none focus:ring-2 focus:ring-blue-500">
          <span className="block truncate">
            {options.find((o) => o.value === sort)?.label}
          </span>
          <span className="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
            <ChevronUpDownIcon className="h-5 w-5 text-gray-400" />
          </span>
        </Listbox.Button>
        <Listbox.Options className="absolute mt-1 w-full rounded-md bg-white dark:bg-gray-800 shadow-lg max-h-60 py-1 text-base overflow-auto focus:outline-none z-10">
          {options.map((option) => (
            <Listbox.Option
              key={option.value}
              value={option.value}
              className={({ active }) =>
                `cursor-pointer select-none relative py-2 pl-10 pr-4 ${
                  active
                    ? 'bg-blue-600 text-white'
                    : 'text-gray-900 dark:text-gray-100'
                }`
              }
            >
              {({ selected }) => (
                <>
                  <span
                    className={`block truncate ${
                      selected ? 'font-semibold' : ''
                    }`}
                  >
                    {option.label}
                  </span>
                  {selected && (
                    <span className="absolute inset-y-0 left-0 flex items-center pl-3 text-blue-600 dark:text-blue-400">
                      <CheckIcon className="h-5 w-5" />
                    </span>
                  )}
                </>
              )}
            </Listbox.Option>
          ))}
        </Listbox.Options>
      </div>
    </Listbox>
  )
}
