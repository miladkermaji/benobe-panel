<div>
  <div class="flex justify-between items-center mb-4">
    <div class="flex items-center space-x-4">
      <div class="relative">
        <input type="text" wire:model.live="search" placeholder="جستجو..."
          class="w-64 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        <div class="absolute left-3 top-2.5">
          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>
      </div>
    </div>
    <a href="{{ route('admin.user-subscriptions.create') }}"
      class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
      افزودن اشتراک جدید
    </a>
  </div>

  <div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
            wire:click="sortBy('id')">
            شناسه
            @if ($sortField === 'id')
              @if ($sortDirection === 'asc')
                ↑
              @else
                ↓
              @endif
            @endif
          </th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
            wire:click="sortBy('user_id')">
            کاربر
            @if ($sortField === 'user_id')
              @if ($sortDirection === 'asc')
                ↑
              @else
                ↓
              @endif
            @endif
          </th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
            wire:click="sortBy('membership_plan_id')">
            طرح عضویت
            @if ($sortField === 'membership_plan_id')
              @if ($sortDirection === 'asc')
                ↑
              @else
                ↓
              @endif
            @endif
          </th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
            wire:click="sortBy('start_date')">
            تاریخ شروع
            @if ($sortField === 'start_date')
              @if ($sortDirection === 'asc')
                ↑
              @else
                ↓
              @endif
            @endif
          </th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
            wire:click="sortBy('end_date')">
            تاریخ پایان
            @if ($sortField === 'end_date')
              @if ($sortDirection === 'asc')
                ↑
              @else
                ↓
              @endif
            @endif
          </th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
            wire:click="sortBy('status')">
            وضعیت
            @if ($sortField === 'status')
              @if ($sortDirection === 'asc')
                ↑
              @else
                ↓
              @endif
            @endif
          </th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
            عملیات
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        @forelse($subscriptions as $subscription)
          <tr>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ $subscription->id }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ $subscription->user->name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ $subscription->membershipPlan->name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ verta($subscription->start_date)->format('Y/m/d') }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ verta($subscription->end_date)->format('Y/m/d') }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $subscription->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $subscription->status ? 'فعال' : 'غیرفعال' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <a href="{{ route('admin.user-subscriptions.edit', $subscription) }}"
                class="text-blue-600 hover:text-blue-900 ml-3">ویرایش</a>
              <button wire:click="delete({{ $subscription->id }})" class="text-red-600 hover:text-red-900"
                onclick="return confirm('آیا از حذف این اشتراک اطمینان دارید؟')">حذف</button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
              هیچ اشتراکی یافت نشد.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $subscriptions->links() }}
  </div>
</div>
