<?php

namespace App\Contracts\Repositories\Admin;

interface DashboardRepositoryInterface
{
    public function getTotalUsersCount(): int;
    public function getTotalMessagesCount(): int;
    public function getLatestMessages(int $limit);
    public function getLatestStations();
    public function getTotalVotersCount(): int;
    public function getAverageVoterAge(): float;
    public function getVotersCountByCounty();
    public function getMessagesAndGroups($user): array;
    public function getStationsAndBlocs(): array;
    public function createPollingStation(array $data);
    public function getCountiesByBloc($blocId);
    public function getCountiesByName($name);
    public function getConstituenciesByCounty($countyName);
    public function getWardsByConstituency($constituencyName);
    public function getPollingStationsFiltered($type, $id);
    public function getPollingStationsByWard($wardName);
    public function getTags();

    public function getTotalGroupsCount();
}
