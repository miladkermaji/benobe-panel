<div>
  <form wire:submit="save" class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label for="user_id" class="block text-sm font-medium text-gray-700">کاربر</label>
        <select wire:model="user_id" id="user_id"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
          <option value="">انتخاب کاربر</option>
          @foreach ($users as $user)
            <option value="{{ $user->id }}">{{ $user->name }}</option>
          @endforeach
        </select>
        @error('user_id')
          <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
      </div>

      <div>
        <label for="membership_plan_id" class="block text-sm font-medium text-gray-700">طرح عضویت</label>
        <select wire:model="membership_plan_id" id="membership_plan_id"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
          <option value="">انتخاب طرح عضویت</option>
          @foreach ($plans as $plan)
            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
          @endforeach
        </select>
        @error('membership_plan_id')
          <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
      </div>

      <div>
        <label for="start_date" class="block text-sm font-medium text-gray-700">تاریخ شروع</label>
        <input type="date" wire:model="start_date" id="start_date"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        @error('start_date')
          <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
      </div>

      <div>
        <label for="end_date" class="block text-sm font-medium text-gray-700">تاریخ پایان</label>
        <input type="date" wire:model="end_date" id="end_date"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        @error('end_date')
          <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
      </div>

      <div>
        <label for="description" class="block text-sm font-medium text-gray-700">توضیحات</label>
        <textarea wire:model="description" id="description" rows="3"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
        @error('description')
          <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
      </div>

      <div class="flex items-center">
        <input type="checkbox" wire:model="status" id="status"
          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
        <label for="status" class="mr-2 block text-sm text-gray-900">فعال</label>
        @error('status')
          <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
      </div>
    </div>

    <div class="flex justify-end space-x-3">
      <a href="{{ route('admin.user-subscriptions.index') }}"
        class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
        انصراف
      </a>
      <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
        ذخیره
      </button>
    </div>
  </form>
</div>
