<?php
namespace App\Services\Parliament;
use App\Models\ParliamentMember;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class ParliamentMemberImportService
{
    public function __construct(private ParliamentMemberMatcher $matcher) {}
    public function importDirectory(array $payload): array
    {
        $rows=$this->listRows($payload);
        if ($rows === []) throw new \RuntimeException('Parliament members directory payload contained no members.');
        $ids=[];
        foreach($rows as $row){
            if(!is_array($row)) continue; $slug=$this->slug($row); $name=trim((string)($row['name']??'')); if($slug===''||$name==='') continue;
            $member=ParliamentMember::firstOrNew(['external_slug'=>$slug]);
            $member->fill(['source_name'=>$name,'normalized_name'=>$this->matcher->normalize($name),'source_url'=>$row['url']??null,'house'=>$row['house']??null,'role'=>$row['role']??null,'constituency'=>$row['constituency']??null]);
            if (! $member->exists || $member->detail_status !== 'complete') $member->raw_payload=$row;
            $member->save();
            $this->matcher->match($member); $ids[]=$member->id;
        }
        return ['received'=>count($rows),'saved'=>count($ids),'ids'=>$ids];
    }
    public function importDetail(ParliamentMember $member,array $payload): void
    {
        $data=$payload['data']??$payload;
        if(!is_array($data) || blank($data['name'] ?? null)) throw new \RuntimeException('Invalid parliament member detail payload.');
        DB::transaction(function()use($member,$data):void{
            $name=trim((string)($data['name']??$member->source_name));
            $member->update(['source_name'=>$name,'normalized_name'=>$this->matcher->normalize($name),'photo_url'=>$data['photo_url']??null,'biography'=>$data['biography']??null,'position_type'=>$data['position_type']??null,'party'=>$data['party']??null,'speeches_last_year'=>$this->integer($data['speeches_last_year']??null),'speeches_total'=>$this->integer($data['speeches_total']??null),'bills_total'=>$this->integer($data['bills_total']??null),'bills_pages'=>$this->integer($data['bills_pages']??null),'raw_payload'=>array_replace_recursive($member->raw_payload ?? [], $data),'detail_status'=>'complete','failure_code'=>null,'detail_fetched_at'=>now()]);
            $member->committees()->delete();
            foreach(array_values(Arr::wrap($data['committees']??[])) as $i=>$committee){if(!is_string($committee)||trim($committee)==='')continue;$member->committees()->create(['name'=>trim($committee),'normalized_name'=>Str::lower(Str::ascii(trim($committee))),'sort_order'=>$i]);}
            $member->activities()->delete();
            $this->activities($member,'position',Arr::wrap($data['positions']??[])); $this->activities($member,'bill',Arr::wrap($data['bills']??[])); $this->activities($member,'speech',Arr::wrap($data['speeches']??[])); $this->activities($member,'vote',Arr::wrap($data['voting_patterns']??[]));
            $this->matcher->match($member->fresh());
        });
    }
    private function activities(ParliamentMember $member,string $type,array $rows):void
    {foreach(array_values($rows) as $i=>$row){$row=is_array($row)?$row:['title'=>(string)$row];$title=trim((string)($row['title']??$row['name']??$row['bill']??$row['speech']??''));if($title==='')continue;$member->activities()->create(['type'=>$type,'occurred_on'=>$this->date($row['date']??null),'title'=>$title,'decision'=>$row['decision']??null,'source_url'=>$row['url']??null,'metadata'=>$row,'sort_order'=>$i]);}}
    private function listRows(array $payload):array{$data=$payload['data']??$payload;if(is_array($data)&&isset($data['members'])&&is_array($data['members']))return array_values($data['members']);return is_array($data)&&array_is_list($data)?$data:[];}
    private function slug(array $row):string{if(filled($row['slug']??null))return Str::slug((string)$row['slug']);return Str::slug((string)basename(trim((string)($row['url']??''),'/')));}
    private function integer(mixed $value):?int{return is_numeric($value)?max(0,(int)$value):null;}
    private function date(mixed $value):?string{if(!is_string($value)||trim($value)==='')return null;try{return Carbon::parse($value)->toDateString();}catch(\Throwable){return null;}}
}