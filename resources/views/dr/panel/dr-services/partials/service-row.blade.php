<tr class="service-row @if($level > 0) subservice @endif" data-id="{{ $service->id }}"
    @if($level > 0) data-parent="{{ $service->parent_id }}" style="display: none;" @endif data-level="{{ $level }}">
    <td>{{ $service->id }}</td>
    <td>
        {!! str_repeat('&mdash; ', $level) !!} {{ $service->name }}
    </td>
    <td>{{ $service->duration }} دقیقه</td>
    <td>{{ number_format($service->price, 0) }} تومان</td>
    <td>{{ $service->discount ? number_format($service->discount, 0) . ' تومان' : 'ندارد' }}</td>
    <td>{{number_format($service->price - $service->discount,0) }}</td>
    <td>
        <span wire:click="toggleStatus({{ $service->id }})"
              class="text-{{ $service->status == 1 ? 'success' : 'danger' }} cursor-pointer">
            {{ $service->status == 1 ? 'فعال' : 'غیرفعال' }}
        </span>
    </td>
    <td>
        <a href="{{ route('dr-services.edit', $service->id) }}" class="btn btn-sm btn-light rounded-circle">
            <img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="ویرایش">
        </a>
        <button type="button" class="btn btn-sm btn-light rounded-circle delete-service"
                data-url="{{ route('dr-services.destroy', $service->id) }}">
            <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
        </button>
    </td>
    <td>
        @if ($service->children->count())
            <button class="btn btn-sm btn-primary toggle-subservices" data-id="{{ $service->id }}">مشاهده</button>
        @endif
    </td>
</tr>

@foreach ($service->children as $child)
    @include('dr.panel.dr-services.partials.service-row', ['service' => $child, 'level' => $level + 1])
@endforeach
