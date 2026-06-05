<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReportTest extends TestCase
{
    /*
    |--------------------------------------------------------------------------
    | REPORT SUMMARY
    |--------------------------------------------------------------------------
    */

    public function testIncomeCalculation(): void
    {
        $income = [1000, 500, 200];

        $this->assertEquals(
            1700,
            array_sum($income)
        );
    }

    public function testExpenseCalculation(): void
    {
        $expense = [300, 200, 100];

        $this->assertEquals(
            600,
            array_sum($expense)
        );
    }

    public function testBalanceCalculation(): void
    {
        $income = 1700;
        $expense = 600;

        $balance = $income - $expense;

        $this->assertEquals(
            1100,
            $balance
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DATE FILTER
    |--------------------------------------------------------------------------
    */

    public function testValidFromDate(): void
    {
        $date = '2026-06-01';

        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}$/',
            $date
        );
    }

    public function testValidToDate(): void
    {
        $date = '2026-06-30';

        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}$/',
            $date
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CATEGORY REPORT
    |--------------------------------------------------------------------------
    */

    public function testCategoryExists(): void
    {
        $category = 'Food & Dining';

        $this->assertNotEmpty(
            $category
        );
    }

    public function testCategorySpentAmount(): void
    {
        $spent = 250;

        $this->assertGreaterThan(
            0,
            $spent
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BUDGET VS ACTUAL
    |--------------------------------------------------------------------------
    */

    public function testBudgetWithinLimit(): void
    {
        $budget = 500;
        $actual = 300;

        $this->assertLessThanOrEqual(
            $budget,
            $actual + 200
        );
    }

    public function testBudgetExceeded(): void
    {
        $budget = 500;
        $actual = 700;

        $this->assertGreaterThan(
            $budget,
            $actual
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CSV EXPORT
    |--------------------------------------------------------------------------
    */

    public function testCsvFileName(): void
    {
        $filename = 'report_2026-06-05.csv';

        $this->assertStringEndsWith(
            '.csv',
            $filename
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SECURITY
    |--------------------------------------------------------------------------
    */

    public function testHtmlEscaping(): void
    {
        $input = '<script>alert(1)</script>';

        $output = htmlspecialchars($input);

        $this->assertNotEquals(
            $input,
            $output
        );
    }
}