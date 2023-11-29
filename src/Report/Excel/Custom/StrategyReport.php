<?php

namespace Optime\Util\Report\Excel\Custom;

use App\Entity\Loyalty\Region;
use App\Entity\Strategy\Strategy;
use App\Repository\Loyalty\RegionRepository;
use Optime\Util\Query\ChallengesReportQuery;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
class StrategyReport
{

    private $styles;

    private $colWidth;


    public function __construct(private ChallengesReportQuery $query, private RegionRepository $repository)
    {
    }

    public function generate(Strategy $strategy)
    {
        $tempDir = sys_get_temp_dir();
        $reportName = 'my_ultimate_challenge.xlsx';

        $this->colWidth = [
            'A' => 24,
            'B' => 14,
            'C' => 17,
            'D' => 24,
            'E' => 40,
            'F' => 40,
            'G' => 12,
            'H' => 20,
            'I' => 40,
            'J' => 14,
            'K' => 14,
            'L' => 18,
        ];

        $this->styles['arrayHeaderStyleGray'] = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => [
                    'argb' => 'A6A6A6',
                ],
            ],
        ];

        $this->styles['arrayHeaderStyleLightGray'] = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => [
                    'argb' => 'BFBFBF',
                ],
            ],
        ];

        $spreadsheet = new Spreadsheet();

        $font = $spreadsheet->getDefaultStyle()->getFont();
        $font->setSize(12);
        $font->setName('Arial');

        $regions = $this->repository->getAllGroupsByLoyalty($strategy->getLoyalty());

        $firstRegion = true;

        foreach ($regions as $region) {

            $normalChallenges = $this->getNormalChallenges($strategy->getId(), $region->getGroup());

            if($normalChallenges){
                $this->generateSheet($spreadsheet, $region->getGroup(), $strategy, $firstRegion);
                $firstRegion = false;
            }
        }

        $normalChallenges = $this->getNormalChallenges($strategy->getId(), null);

        if($normalChallenges){
            $this->generateSheet($spreadsheet, null, $strategy, false);
        }

        // Save the spreadsheet as an Excel file
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempDir . '/'.$reportName);

        // Return the file path
        return ['path' => $tempDir, 'reportName' => $reportName];
    }

    public function generateSheet(Spreadsheet $spreadsheet, $regionGroup, Strategy $strategy, $firstRegion)
    {
        $result = $this->getDataArray($strategy->getId(), $regionGroup);

        if($firstRegion){
            $sheet = $spreadsheet->getActiveSheet();
        }else{
            $sheet = $spreadsheet->createSheet();
        }

        // Set the sheet title
        $sheetName = (is_null($regionGroup)) ? 'All Regions' : $regionGroup;
        $sheet->setTitle($sheetName);

        // Add some data to the sheet
        $sheet->setCellValue('A1', $strategy->getLoyalty()->getName());
        $sheet->setCellValue('A2', 'My Ultimate Challenge Report');
        $sheet->setCellValue('A3', 'Downloaded at: ' . date('m-d-Y'));

        $data = $result['data'];

        $firstRow = $data[0];

        $headers = array_keys($firstRow);

        $currentRow = 6;
        $currentColumn = 0;

        $normalChallenges = $this->getNormalChallenges($strategy->getId(), $regionGroup);

        $accuChallenges = $this->getAccuChallenges($strategy->getId(), $regionGroup);

        foreach ($headers as $columnId) {

            $currentColumn++;

            $columnName = $columnId;

            foreach ($normalChallenges as $challengeId => $challenge) {
                if ($challengeId == $columnId) {
                    $columnName = $challenge['name'];
                    break;
                }
                foreach ($challenge['badges'] as $badgeId => $badge) {
                    if ($columnId == $badgeId) {
                        $columnName = $badge;
                        break;
                    }
                }
            }

            if (isset($accuChallenges[$columnId])) {
                $columnName = $accuChallenges[$columnId];
            }

            if (isset($result['merge'][$columnId])) {
                $cColumnLetter = Coordinate::stringFromColumnIndex($currentColumn - $result['merge'][$columnId]);
                $columnLetter = Coordinate::stringFromColumnIndex($currentColumn);
                $sheet->setCellValueExplicit($cColumnLetter . '5', $columnName, DataType::TYPE_STRING);
                $range = $cColumnLetter . '5:' . $columnLetter . '5';
                $sheet->mergeCells($range);
                $sheet->getStyle($range)->applyFromArray($this->styles['arrayHeaderStyleLightGray']);
                $columnName = 'Completed';
            }

            $columnLetter = Coordinate::stringFromColumnIndex($currentColumn);
            $sheet->setCellValueExplicit($columnLetter . $currentRow, $columnName, DataType::TYPE_STRING);

            $calculatedWidth = strlen($columnName) * 0.90 + 1;
            $calculatedWidth = ($calculatedWidth > 47) ? 47 : $calculatedWidth;
            if(isset($this->colWidth[$currentColumn])){
                $sheet->getColumnDimensionByColumn($currentColumn)->setWidth($this->colWidth[$currentColumn]);
            }else{
                $sheet->getColumnDimensionByColumn($currentColumn)->setAutoSize(true);
            }
        }

        $headerStyleRange = 'A6:' . $columnLetter . '6';

        $sheet->getStyle($headerStyleRange)->applyFromArray($this->styles['arrayHeaderStyleGray']);

        $currentRow = 6;
        foreach ($data as $row) {
            $currentRow++;
            $currentColumn = 0;
            foreach ($row as $value) {
                $currentColumn++;
                $columnLetter = Coordinate::stringFromColumnIndex($currentColumn);
                $sheet->setCellValueExplicit($columnLetter . $currentRow, $value, DataType::TYPE_STRING);
            }
        }
    }

    public function getDataArray($strategyId, $regionGroup)
    {

        $normalChallenges = $this->getNormalChallenges($strategyId, $regionGroup);

        $mergeBadges = [];
        foreach ($normalChallenges as $challenge => $badges){
            $mergeBadges[$challenge] = count($badges['badges']);
        }

        $completedBadgesData = $this->query->getCompletedBadges($strategyId, $regionGroup);

        $completedBadges = [];
        foreach ($completedBadgesData as $badge) {
            $completedBadges[$badge['challenge_id']][$badge['challenge_id'].'-'.$badge['badge_id']][$badge['user_id']] = true;
        }

        $accuChallenges = $this->getAccuChallenges($strategyId, $regionGroup);

        $completedChallengesData = $this->query->getCompletedChallenges($strategyId, $regionGroup);

        $completedChallenges = [];
        foreach ($completedChallengesData as $challenge) {
            $completedChallenges[$challenge['challenge_id']][$challenge['user_id']] = true;
        }

        $data = $this->query->usersData($strategyId, $regionGroup);

        foreach ($data as $key => $row) {
            foreach ($normalChallenges as $challenge => $badges){
                $lastKey = array_key_last($badges['badges']);
                foreach ($badges['badges'] as $badgeKey => $badge){
                    $data[$key][$badgeKey] = (isset($completedBadges[$challenge][$badgeKey][$row['id']]) and $completedBadges[$challenge][$badgeKey][$row['id']]) ? 'YES' : 'NO';
                    if($badgeKey === $lastKey){
                        $data[$key][$challenge] = (isset($completedChallenges[$challenge][$row['id']]) and $completedChallenges[$challenge][$row['id']]) ? 'YES' : 'NO';
                    }
                }
            }
            foreach ($accuChallenges as $challengeId => $challenge){
                $data[$key][$challengeId] = (isset($completedChallenges[$challengeId][$row['id']]) and $completedChallenges[$challengeId][$row['id']]) ? 'YES' : 'NO';
            }
            unset($data[$key]['id']);
        }

        return ['data' => $data, 'merge' => $mergeBadges];
    }

    public function getNormalChallenges($strategyId, $regionGroup)
    {
        $normalChallengesData = $this->query->getNormalChallenges($strategyId, $regionGroup);

        $normalChallenges = [];
        foreach ($normalChallengesData as $badge){
            $normalChallenges[$badge['challenge_id']]['badges'][$badge['challenge_id'].'-'.$badge['badge_id']] = str_replace('&nbsp;', '', strip_tags($badge['badge_internal_name']));
            $normalChallenges[$badge['challenge_id']]['name'] = strip_tags($badge['challenge_internal_name']);
        }

        return $normalChallenges;
    }

    public function getAccuChallenges($strategyId, $regionGroup)
    {

        $accuChallengeData = $this->query->getAccuChallenges($strategyId, $regionGroup);
        $accuChallenges = [];
        foreach ($accuChallengeData as $challenge){
            $accuChallenges[$challenge['challenge_id']] = str_replace('&nbsp;', '', strip_tags($challenge['challenge_internal_name']));
        }

        return $accuChallenges;
    }
}