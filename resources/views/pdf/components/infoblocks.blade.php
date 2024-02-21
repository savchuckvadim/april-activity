<div class="infoblocks">
    <div class="infoblocks-wrapper">
        @if ($styleMode == 'list')
        @foreach ($complect as $group)
        @foreach ($group['value'] as $infoblock)
        @if (array_key_exists('code', $infoblock))
        @php
        $currentInfoblock = $infoblocks->get($infoblock['code']);
        @endphp
        @if ($currentInfoblock)


        @if ($descriptionMode === 0)
        @if (!empty($infoblock['code']) && $currentInfoblock = $infoblocks->get($infoblock['code']))
        <p>
            {{$currentInfoblock['name']}}
        </p>
        @endif

        @elseif ($descriptionMode === 1)
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


        @elseif ($styleMode == 'table')




        @elseif ($styleMode == 'tableWithGroup')

        @endif

    </div>