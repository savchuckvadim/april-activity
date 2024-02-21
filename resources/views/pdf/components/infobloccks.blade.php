<div class="infoblocks">
    <div class="infoblocks-wrapper">
        @if ($styleMode == 'list')
        @foreach ($complect as $group)
        @foreach ($group['value'] as $infoblock)
        @if (array_key_exists('code', $infoblock))
        @php
        $currentInfoblock = Infoblock::where('code', $infoblock['code'])->first();
        @endphp
        @if ($currentInfoblock)


        @if ($descriptionMode === 0)
        <p>
            {{$currentInfoblock['name']}}
        </p>
        @else if ($descriptionMode === 1)
        <h2>
            {{$currentInfoblock['name']}}
        </h2>
        <p>
            {{$currentInfoblock['shortDescription']}}
        </p>
        @else
        <h2>
            {{$currentInfoblock['name']}}
        </h2>
        <p>
            {{$currentInfoblock['descriptionForSale']}}
        </p>
        @endif



        @endif
       
        @endif
        @endforeach


        @endforeach


        @else if ($styleMode == 'table')




        @else if ($styleMode == 'tableWithGroup')

        @endif

    </div>