<?php
namespace App\Services\Parliament;
use App\Models\Candidate;
use App\Models\ParliamentMember;
use Illuminate\Support\Str;
class ParliamentMemberMatcher
{
    private const IGNORED = ['hon','honourable','dr','mr','mrs','ms','prof','professor','mp','senator'];
    public function normalize(string $name): string { return implode(' ', $this->tokens($name)); }
    public function match(ParliamentMember $member): void
    {
        if ($member->candidate_id || $member->match_method === 'manual') return;
        $tokens = $this->tokens($member->source_name);
        if (count($tokens) < 3) return;
        $matches = Candidate::query()->select(['id','name','nick_name'])->get()->map(function (Candidate $candidate) use ($tokens): array {
            $candidateTokens = array_values(array_unique(array_merge($this->tokens($candidate->name), $this->tokens((string) $candidate->nick_name))));
            return ['candidate' => $candidate, 'count' => count(array_intersect($tokens, $candidateTokens))];
        })->filter(fn (array $match): bool => $match['count'] >= 3)->sortByDesc('count')->values();
        if ($matches->isEmpty()) return;
        $best = $matches->first();
        if ($matches->where('count', $best['count'])->count() !== 1 || ParliamentMember::where('candidate_id', $best['candidate']->id)->whereKeyNot($member->id)->exists()) {
            $member->update(['match_method' => 'ambiguous', 'matched_token_count' => $best['count']]); return;
        }
        $member->update(['candidate_id'=>$best['candidate']->id,'match_method'=>'automatic','matched_token_count'=>$best['count'],'linked_at'=>now(),'linked_by'=>null,'is_published'=>false,'published_at'=>null,'published_by'=>null]);
    }
    private function tokens(string $name): array
    {
        $value = preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii($name))) ?: '';
        return collect(explode(' ', trim($value)))->filter(fn (string $token): bool => strlen($token)>1 && !in_array($token,self::IGNORED,true))->unique()->sort()->values()->all();
    }
}