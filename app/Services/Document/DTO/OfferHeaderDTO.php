<?php
class OfferHeaderDTO
{
    public bool $isTwoLogo;
    public string $rq;
    public ?string $logo_1;
    public ?string $logo_2;

    public function __construct(array $data)
    {
        $this->isTwoLogo = $data['isTwoLogo'] ?? false;
        $this->rq = $data['rq'] ?? '';
        $this->logo_1 = $data['logo_1'] ?? null;
        $this->logo_2 = $data['logo_2'] ?? null;
    }
}
