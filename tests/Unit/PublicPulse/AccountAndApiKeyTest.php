<?php
namespace Tests\Unit\PublicPulse;
use App\Contracts\Repositories\Web\PublicPulseSourceAccountRepositoryInterface;
use App\Http\Middleware\EnsurePulseEngineApiKey;
use App\Models\PublicPulseSourceAccount;
use App\Services\PublicPulse\PublicPulseAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
class AccountAndApiKeyTest extends TestCase
{
    public function test_api_key_rejects_missing_and_invalid_and_accepts_valid(): void
    {
        config(['services.pulse_engine.api_key'=>'shared-secret']);
        $middleware = new EnsurePulseEngineApiKey;
        foreach ([null,'wrong'] as $key) {
            $request = Request::create('/api/scraper/accounts');
            if ($key) $request->headers->set('X-Api-Key',$key);
            $this->assertSame(401,$middleware->handle($request,fn()=>new Response('',204))->getStatusCode());
        }
        $request = Request::create('/api/scraper/accounts');
        $request->headers->set('X-Api-Key','shared-secret');
        $this->assertSame(204,$middleware->handle($request,fn()=>new Response('',204))->getStatusCode());
    }
    public function test_engine_accounts_expose_only_required_cookies_and_skip_invalid_payloads(): void
    {
        $valid = new PublicPulseSourceAccount(['provider'=>'x_twscrape','label'=>'Primary','username'=>'pulse_x','encrypted_session_payload'=>json_encode(['cookies'=>[['name'=>'auth_token','value'=>'token'],['name'=>'ct0','value'=>'csrf'],['name'=>'other','value'=>'secret']]])]);
        $valid->id=7;
        $invalid = new PublicPulseSourceAccount(['provider'=>'x_twscrape','label'=>'Bad','encrypted_session_payload'=>json_encode(['auth_token'=>'only'])]);
        $repo = $this->createMock(PublicPulseSourceAccountRepositoryInterface::class);
        $repo->expects($this->once())->method('activeForProvider')->with('x_twscrape',10)->willReturn(new Collection([$valid,$invalid]));
        $accounts=(new PublicPulseAccountService($repo))->engineAccounts();
        $this->assertSame([['id'=>7,'username'=>'pulse_x','auth_token'=>'token','ct0'=>'csrf']],$accounts);
    }
}
