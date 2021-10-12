<div class="flex flex-col">
  <div class="text-xl font-semibold">
    <a href="{{ route('dashboard', array_filter(array_merge($filters, [
        'from' => (new \Carbon\Carbon)->format('Y-m-d'),
        'days' => '1',
      ]))) }}"
    >
      Heute
    </a>
  </div>
  <div class="text-xl font-semibold">
    <a href="{{ route('dashboard', array_filter(array_merge($filters, [
        'from' => (new \Carbon\Carbon)->startOfWeek()->format('Y-m-d'),
        'days' => '7'
      ]))) }}"
    >
      Diese Woche
    </a>
  </div>
  <div>
    <div class="text-xl font-semibold border-t-2 mt-2 pt-2">Springe zu</div>
    <form action="?" method="GET">
      <x-input type="date" name="from" id="" value="{{ $filters['from'] ?? '' }}" class="w-full" />
      <div class="my-2 flex justify-between">
        <x-button class="flex-1 mr-2" type="submit" name="days" value="1">Tag</x-button>
        <x-button class="flex-1" type="submit" name="days" value="7">Woche</x-button>
      </div>
    </form>
  </div>
  <div class="border-t-2 my-2 py-2">
    <div class="text-xl">Kundensuche</div>
    <livewire:customer-search />
  </div>
  <div class="border-t-2 my-2 py-2">
    <div class="text-xl">
      <a href="{{ route('dashboard', ['check' => true]) }}">
        Checks
      </a>
    </div>
    @can ('modify orders')
      @if ($due = $venues->where('check_count', '>', 0)->count())
        <ul>
          @foreach ($due as $item)
            <li>
              <a href="{{ route('dashboard', array_filter([
                  'check' => true,
                  'venue' => $item->id
                ])) }}">
                {{ $item->name }}: {{ $item->check_count }}
              </a>
            </li>
          @endforeach
        </ul>
      @else
        Heute keine Checks
      @endif
    @endcan
  </div>
</div>
