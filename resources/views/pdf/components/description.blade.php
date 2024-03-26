<style>
    .descrip-wrapper,
    h3,
    .info-complectName {
        margin-bottom: 12px;
        text-align: justify;
    }






    .descrip-group-title {
        margin-top: 25px;
        margin-bottom: 10px;
        text-align: center;
    }

    .infoblock-big-group-title {

        text-align: center;
        margin-top: 8px;
        margin-bottom: 4px;

    }

    .descrip-iblock-title {
        font-size: 10px;
        margin-top: 8px;
        margin-bottom: 4px;

    }

    .descrip-text{
        width: 100%;
        text-align: justify;

    }
</style>

<div class="infoblocks-big-description">

    <div class="descrip-wrapper">
        @php
            $lastGroupName = null;
            $lastInfoblockName = null;
        @endphp

        @foreach ($pages as $index => $page)
            <div class="page-content">
                @if ($index == 0)
                    <h3>Описание Информационного наполнения </h3>
                    <p class="text-normal info-complectName color">{{ $complectName }}</p>
                @endif
                @foreach ($page['groups'] as $group)
                    @if ($group['name'] !== $lastGroupName)
                        <p class="{{ 'text-large descrip-group-title color' }}">
                            {{ $group['name'] }}
                        </p>
                        @php
                            $lastGroupName = $group['name'];
                        @endphp
                    @endif


                    @foreach ($group['items'] as $infoblock)
                        @if ($infoblock['name'] !== $lastInfoblockName)
                            <p class="descrip-iblock-title bold">
                                {{ $infoblock['name'] }}
                            </p>
                        @endif
                        @php
                            $lastInfoblockName = $infoblock['name'];
                        @endphp

                        <p class="text-normal descrip-text small">
                            {!! nl2br(e($infoblock['description'])) !!}
                        </p>
                    @endforeach
                @endforeach

                @if (!$loop->last)
                    <div class="page-break"></div>
                @endif

            </div>
        @endforeach


    </div>
