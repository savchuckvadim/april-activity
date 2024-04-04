<?php

namespace App\Jobs;

use App\Services\BitrixDealUpdateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BitrixDealUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $domain;
    protected $placement;
    
    protected $dealId;
    protected $updateDealInfoblocksData;
    protected $updateDealContractData;
    protected $setProductRowsData;
    protected $updateProductRowsData;
    public function __construct(
        $domain,
        $placement,
        $dealId,
        $updateDealInfoblocksData,
        $updateDealContractData,
        $setProductRowsData,
        $updateProductRowsData
    ) {
        $this->domain =  $domain;
        $this->placement =  $placement;
        $this->dealId =  $dealId;
        $this->updateDealInfoblocksData =  $updateDealInfoblocksData;
        $this->updateDealContractData =  $updateDealContractData;


        $this->setProductRowsData =  $setProductRowsData;
        $this->updateProductRowsData =  $updateProductRowsData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = new BitrixDealUpdateService(
            $this->domain,
            $this->placement,
            $this->dealId,
            $this->updateDealInfoblocksData,
            $this->updateDealContractData,
            $this->setProductRowsData,
            $this->updateProductRowsData

        );
        $service->dealProccess();
    }
}
