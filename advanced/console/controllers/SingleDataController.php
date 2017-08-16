<?php

/**
 * Created by PhpStorm.
 * User: Allen
 * Date: 08/05/2017
 * Time: 10:04 AM
 */

namespace console\controllers;

use common\consts\Consts;
use common\models\PostOverdueCompensation;
use common\models\RateConfig;
use yii\console\Controller;
use common\models\PostChannelLoan;
use common\models\PostChannelRepay;
use common\models\Asset;
use common\helpers\StringUtil;
use common\helpers\DbUtil;
use common\helpers\Dh;
use Yii;

class SingleDataController extends Controller
{

    /**
     * 根据基础表把数据刷入post_channel_loan
     *
     */
    public function actionAddLoan()
    {
        $argv = func_get_args();
        $yesterday = Dh::yesterdayDate();
        $now = Dh::getcurrentDateTime();
        $whereTime = isset($argv[0]) && !empty($argv[0]) ? $argv[0] : $yesterday;

        $sqlSelect = "select * from post_channel_loan where datediff(post_channel_loan_date,'$whereTime') = 0";
        $res = Yii::$app->db->createCommand($sqlSelect)->queryAll();
        if (!empty($res)) {
            die('数据已经导入过，不能重复执行！');
        }

        $sqlStart = "select asset_id from asset left join `grant` on grant_asset_id = asset_id where datediff(grant_finish_at,'$whereTime') = 0 order by asset_id asc limit 1";
        $min_id = Yii::$app->db->createCommand($sqlStart)->queryScalar();

        $sqlEnd = "select asset_id from asset left join `grant` on grant_asset_id = asset_id where datediff(grant_finish_at,'$whereTime') = 0 order by asset_id desc limit 1";
        $max_id = Yii::$app->db->createCommand($sqlEnd)->queryScalar();

        if (!$min_id || !$max_id) {
            die('没有符合导入条件的数据');
        }

        $max_id += 1;
        $num = ceil(($max_id - $min_id) / 1000);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            for ($i = 1; $i <= $num; $i++) {
                $start_limit = ($i - 1) * 1000 + $min_id;
                $end_limit = $i * 1000 + $min_id;
                if ($i == $num) {
                    $end_limit = $max_id;
                }

                echo "start--".round((memory_get_usage()/1024/1024), 2),"M...memory \r\n";

                $sql1 = "select grant_finish_at,asset_id,asset_type,asset_sub_type,asset_period_count,asset_period_days,grant_channel,asset_from_system,
                grant_creditor,asset_principal_amount,asset_principal_amount*0.07,0.07,'$now' from asset left join `grant` on grant_asset_id = asset_id where asset_id >= $start_limit and asset_id < $end_limit and datediff(grant_finish_at,'$whereTime') = 0";

                $sql = "INSERT INTO post_channel_loan (post_channel_loan_date,post_channel_loan_asset_id,post_channel_loan_asset_type,post_channel_loan_asset_sub_type,post_channel_loan_asset_period_count,post_channel_loan_asset_period_days,post_channel_loan_asset_loan_channel,
                post_channel_loan_asset_from_system,post_channel_loan_asset_fund_provider,post_channel_loan_principal_amount,post_channel_loan_allowance_amount,post_channel_loan_allowance_rate,post_channel_loan_create_at) $sql1";

                Yii::$app->db->createCommand($sql)->execute();

                echo "end--".round((memory_get_usage()/1024/1024), 2),"M...memory \r\n";
            }

            $transaction->commit();
            die('执行成功');
        } catch (\Exception $e) {
            $transaction->rollBack();
            die('执行失败：'.$e->getMessage());
        }
    }

    /**
     * 根据基础表把数据刷入post_channel_repay
     *
     */
    public function actionAddRepay()
    {
        $argv = func_get_args();
        $yesterday = Dh::yesterdayDate();
        $now = Dh::getcurrentDateTime();
        $whereTime = isset($argv[0]) && !empty($argv[0]) ? $argv[0] : $yesterday;

        $sqlSelect = "select * from post_channel_repay where post_channel_repay_asset_sub_type = 'single' and datediff(post_channel_repay_date,'$whereTime') = 0";
        $res = Yii::$app->db->createCommand($sqlSelect)->queryAll();
        if (!empty($res)) {
            die('数据已经导入过，不能重复执行！');
        }

        $sqlStart = "select asset_id from asset
        left join `grant` on grant_asset_id = asset_id
        left join repay on repay_asset_id = asset_id
        left join repay_detail on repay_detail_repay_id = repay_id
        where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and repay_detail_repay_type in ('repay','reverse') and datediff(repay_detail_finish_at,'$whereTime') = 0 group by asset_id order by asset_id asc limit 1";
        $min_id = Yii::$app->db->createCommand($sqlStart)->queryScalar();

        $sqlEnd = "select asset_id from asset
        left join `grant` on grant_asset_id = asset_id
        left join repay on repay_asset_id = asset_id
        left join repay_detail on repay_detail_repay_id = repay_id
        where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and repay_detail_repay_type in ('repay','reverse') and datediff(repay_detail_finish_at,'$whereTime') = 0 group by asset_id order by asset_id desc limit 1";
        $max_id = Yii::$app->db->createCommand($sqlEnd)->queryScalar();

        if (!$min_id || !$max_id) {
            die('没有符合导入条件的数据');
        }

        $max_id += 1;
        $num = ceil(($max_id - $min_id) / 1000);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            for ($i = 1; $i <= $num; $i++) {
                $start_limit = ($i - 1) * 1000 + $min_id;
                $end_limit = $i * 1000 + $min_id;
                if ($i == $num) {
                    $end_limit = $max_id;
                }

                echo "start--".round((memory_get_usage()/1024/1024), 2),"M...memory \r\n";

                //提前还款的数据
                $sql1 = "select a.repay_detail_finish_at,a.asset_id,a.asset_type,a.asset_sub_type,a.asset_period_count,a.asset_period_days,a.grant_channel,
                a.asset_from_system,a.grant_creditor,a.sum_principal,a.sum_interest,ifnull(b.sum_service_amount,0) as sum_service_amount,0,'advance',
                ifnull(c.sum_manage_amount,0) as sum_manage_amount,1,is_day,'$now'
                from (
                (select asset_id,repay_detail_finish_at,asset_type,asset_sub_type,asset_period_count,asset_period_days,grant_channel,asset_from_system,grant_creditor,count(1) as num,
                sum(if(repay_detail_repay_type = 'repay',repay_detail_principal_amount,0)) - sum(if(repay_detail_repay_type = 'reverse',repay_detail_principal_amount,0)) as sum_principal,
                sum(if(repay_detail_repay_type = 'repay',repay_detail_interest_amount,0)) - sum(if(repay_detail_repay_type = 'reverse',repay_detail_interest_amount,0)) as sum_interest,
                sum(if(repay_detail_repay_type = 'repay',repay_detail_decrease_amount,0)) - sum(if(repay_detail_repay_type = 'reverse',repay_detail_decrease_amount,0)) as sum_decrease,
                datediff(repay_detail_finish_at,repay_expect_finish_at) as is_day
                from asset
                left join `grant` on grant_asset_id = asset_id
                left join repay on repay_asset_id = asset_id
                left join repay_detail on repay_detail_repay_id = repay_id
                where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and repay_detail_repay_type in ('repay','reverse') and datediff(repay_detail_finish_at,repay_expect_finish_at) < 0 group by asset_id) as a
                left join
                (select asset_id,fee_repay_detail_finish_at,
                sum(if(fee_repay_detail_repay_type = 'repay',fee_repay_detail_amount,0)) - sum(if(fee_repay_detail_repay_type = 'reverse',fee_repay_detail_amount,0)) as sum_service_amount,
                datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) as f_day
                from asset
                left join fee on fee_asset_id = asset_id
                left join fee_repay on fee_repay_fee_id = fee_id
                left join fee_repay_detail on fee_repay_detail_fee_repay_id = fee_repay_id
                where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and fee_repay_detail_repay_type in ('repay','reverse') and datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) < 0 group by asset_id) as b
                on a.asset_id = b.asset_id
                left join
                (select asset_id,
                sum(if(fee_repay_detail_repay_type = 'repay',fee_repay_detail_amount,0)) - sum(if(fee_repay_detail_repay_type = 'reverse',fee_repay_detail_amount,0)) as sum_manage_amount,
                datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) as f_day
                from asset
                left join fee on fee_asset_id = asset_id and fee_type = 'manage'
                left join fee_repay on fee_repay_fee_id = fee_id
                left join fee_repay_detail on fee_repay_detail_fee_repay_id = fee_repay_id
                where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and fee_repay_detail_repay_type in ('repay','reverse') and datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) < 0 group by asset_id) as c
                on a.asset_id = c.asset_id )
                where (a.asset_id >= $start_limit) AND (a.asset_id < $end_limit) and datediff(a.repay_detail_finish_at,'$whereTime') = 0";

                //正常还款的数据
                $sql2 = "select a.repay_detail_finish_at,a.asset_id,a.asset_type,a.asset_sub_type,a.asset_period_count,a.asset_period_days,a.grant_channel,
                a.asset_from_system,a.grant_creditor,a.sum_principal,a.sum_interest,ifnull(b.sum_service_amount,0) as sum_service_amount,0,'normal',
                ifnull(c.sum_manage_amount,0) as sum_manage_amount,1,is_day,'$now'
                from (
                (select asset_id,repay_detail_finish_at,asset_type,asset_sub_type,asset_period_count,asset_period_days,grant_channel,asset_from_system,grant_creditor,count(1) as num,
                sum(if(repay_detail_repay_type = 'repay',repay_detail_principal_amount,0)) - sum(if(repay_detail_repay_type = 'reverse',repay_detail_principal_amount,0)) as sum_principal,
                sum(if(repay_detail_repay_type = 'repay',repay_detail_interest_amount,0)) - sum(if(repay_detail_repay_type = 'reverse',repay_detail_interest_amount,0)) as sum_interest,
                sum(if(repay_detail_repay_type = 'repay',repay_detail_decrease_amount,0)) - sum(if(repay_detail_repay_type = 'reverse',repay_detail_decrease_amount,0)) as sum_decrease,
                datediff(repay_detail_finish_at,repay_expect_finish_at) as is_day
                from asset
                left join `grant` on grant_asset_id = asset_id
                left join repay on repay_asset_id = asset_id
                left join repay_detail on repay_detail_repay_id = repay_id
                where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and repay_detail_repay_type in ('repay','reverse') and datediff(repay_detail_finish_at,repay_expect_finish_at) = 0 group by asset_id) as a
                left join
                (select asset_id,fee_repay_detail_finish_at,
                sum(if(fee_repay_detail_repay_type = 'repay',fee_repay_detail_amount,0)) - sum(if(fee_repay_detail_repay_type = 'reverse',fee_repay_detail_amount,0)) as sum_service_amount,
                datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) as f_day
                from asset
                left join fee on fee_asset_id = asset_id
                left join fee_repay on fee_repay_fee_id = fee_id
                left join fee_repay_detail on fee_repay_detail_fee_repay_id = fee_repay_id
                where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and fee_repay_detail_repay_type in ('repay','reverse') and datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) = 0 group by asset_id) as b
                on a.asset_id = b.asset_id
                left join
                (select asset_id,
                sum(if(fee_repay_detail_repay_type = 'repay',fee_repay_detail_amount,0)) - sum(if(fee_repay_detail_repay_type = 'reverse',fee_repay_detail_amount,0)) as sum_manage_amount,
                datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) as f_day
                from asset
                left join fee on fee_asset_id = asset_id and fee_type = 'manage'
                left join fee_repay on fee_repay_fee_id = fee_id
                left join fee_repay_detail on fee_repay_detail_fee_repay_id = fee_repay_id
                where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and fee_repay_detail_repay_type in ('repay','reverse') and datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) = 0 group by asset_id) as c
                on a.asset_id = c.asset_id )
                where (a.asset_id >= $start_limit) AND (a.asset_id < $end_limit) and datediff(a.repay_detail_finish_at,'$whereTime') = 0";

                //逾期还款的数据
                $sql3 = "select a.repay_detail_finish_at,a.asset_id,a.asset_type,a.asset_sub_type,a.asset_period_count,a.asset_period_days,a.grant_channel,
                a.asset_from_system,a.grant_creditor,a.sum_principal,a.sum_interest,ifnull(b.sum_service_amount,0) as sum_service_amount,
                ifnull(d.sum_late_amount,0) as sum_late_amount,'compensation',ifnull(c.sum_manage_amount,0) as sum_manage_amount,1,is_day,'$now'
                from (
                (select asset_id,repay_detail_finish_at,asset_type,asset_sub_type,asset_period_count,asset_period_days,grant_channel,asset_from_system,grant_creditor,count(1) as num,  
                sum(if(repay_detail_repay_type = 'repay',repay_detail_principal_amount,0)) - sum(if(repay_detail_repay_type = 'reverse',repay_detail_principal_amount,0)) as sum_principal,
                sum(if(repay_detail_repay_type = 'repay',repay_detail_interest_amount,0)) - sum(if(repay_detail_repay_type = 'reverse',repay_detail_interest_amount,0)) as sum_interest,
                sum(if(repay_detail_repay_type = 'repay',repay_detail_decrease_amount,0)) - sum(if(repay_detail_repay_type = 'reverse',repay_detail_decrease_amount,0)) as sum_decrease,  
                datediff(repay_detail_finish_at,repay_expect_finish_at) as is_day
                from asset
                left join `grant` on grant_asset_id = asset_id
                left join repay on repay_asset_id = asset_id
                left join repay_detail on repay_detail_repay_id = repay_id 
                where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and repay_detail_repay_type in ('repay','reverse') and datediff(repay_detail_finish_at,repay_expect_finish_at) > 0 group by asset_id) as a
                left join
                (select asset_id,fee_repay_detail_finish_at,  
                sum(if(fee_repay_detail_repay_type = 'repay',fee_repay_detail_amount,0)) - sum(if(fee_repay_detail_repay_type = 'reverse',fee_repay_detail_amount,0)) as sum_service_amount,
                datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) as f_day
                from asset
                left join fee on fee_asset_id = asset_id
                left join fee_repay on fee_repay_fee_id = fee_id
                left join fee_repay_detail on fee_repay_detail_fee_repay_id = fee_repay_id
                where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and fee_repay_detail_repay_type in ('repay','reverse') and datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) > 0 group by asset_id) as b
                on a.asset_id = b.asset_id
                left join
                (select asset_id,  
                sum(if(fee_repay_detail_repay_type = 'repay',fee_repay_detail_amount,0)) - sum(if(fee_repay_detail_repay_type = 'reverse',fee_repay_detail_amount,0)) as sum_manage_amount,
                datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) as f_day
                from asset
                left join fee on fee_asset_id = asset_id and fee_type = 'manage'
                left join fee_repay on fee_repay_fee_id = fee_id
                left join fee_repay_detail on fee_repay_detail_fee_repay_id = fee_repay_id
                where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and fee_repay_detail_repay_type in ('repay','reverse') and datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) > 0 group by asset_id) as c
                on a.asset_id = c.asset_id 
                left join
                (select asset_id,late_fee_repay_detail_finish_at,  
                sum(if(late_fee_repay_detail_repay_type = 'repay',late_fee_repay_detail_amount,0)) - sum(if(late_fee_repay_detail_repay_type = 'reverse',late_fee_repay_detail_amount,0)) as sum_late_amount
                from asset
                left join late_fee on late_fee_asset_id = asset_id
                left join late_fee_repay on late_fee_repay_fee_id = late_fee_id
                left join late_fee_repay_detail on late_fee_repay_detail_fee_repay_id = late_fee_repay_id
                where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and late_fee_repay_detail_repay_type in ('repay','reverse') group by asset_id) as d
                on a.asset_id = d.asset_id )
                where (a.asset_id >= $start_limit) AND (a.asset_id < $end_limit) and datediff(a.repay_detail_finish_at,'$whereTime') = 0";

                $sqlStr1 = "INSERT INTO post_channel_repay (post_channel_repay_date,post_channel_repay_asset_id,post_channel_repay_asset_type,post_channel_repay_asset_sub_type,post_channel_repay_asset_period_count,post_channel_repay_asset_period_days,
                post_channel_repay_asset_loan_channel,post_channel_repay_asset_from_system,post_channel_repay_asset_fund_provider,post_channel_repay_principal_amount,
                post_channel_repay_interest_amount,post_channel_repay_service_amount,post_channel_repay_late_amount,post_channel_repay_type,post_channel_repay_trial_amount,
                post_channel_repay_quantity,post_channel_repay_late_day,post_channel_repay_create_at) $sql1";

                $sqlStr2 = "INSERT INTO post_channel_repay (post_channel_repay_date,post_channel_repay_asset_id,post_channel_repay_asset_type,post_channel_repay_asset_sub_type,post_channel_repay_asset_period_count,post_channel_repay_asset_period_days,
                post_channel_repay_asset_loan_channel,post_channel_repay_asset_from_system,post_channel_repay_asset_fund_provider,post_channel_repay_principal_amount,
                post_channel_repay_interest_amount,post_channel_repay_service_amount,post_channel_repay_late_amount,post_channel_repay_type,post_channel_repay_trial_amount,
                post_channel_repay_quantity,post_channel_repay_late_day,post_channel_repay_create_at) $sql2";

                $sqlStr3 = "INSERT INTO post_channel_repay (post_channel_repay_date,post_channel_repay_asset_id,post_channel_repay_asset_type,post_channel_repay_asset_sub_type,post_channel_repay_asset_period_count,post_channel_repay_asset_period_days,
                post_channel_repay_asset_loan_channel,post_channel_repay_asset_from_system,post_channel_repay_asset_fund_provider,post_channel_repay_principal_amount,
                post_channel_repay_interest_amount,post_channel_repay_service_amount,post_channel_repay_late_amount,post_channel_repay_type,post_channel_repay_trial_amount,
                post_channel_repay_quantity,post_channel_repay_late_day,post_channel_repay_create_at) $sql3";

                Yii::$app->db->createCommand($sqlStr1)->execute();
                Yii::$app->db->createCommand($sqlStr2)->execute();
                Yii::$app->db->createCommand($sqlStr3)->execute();

                echo "end--".round((memory_get_usage()/1024/1024), 2),"M...memory \r\n";
            }

            $transaction->commit();
            die('执行成功');
        } catch (\Exception $e) {
            $transaction->rollBack();
            die('执行失败：'.$e->getMessage());
        }
    }

    /**
     * 一次性把数据刷入post_channel_loan
     *
     */
    public function actionAllImportLoan()
    {
        $max_id = Asset::find()
            ->select(['asset_id'])
            ->orderBy(['asset_id' => SORT_DESC])
            ->limit(1)
            ->asArray()
            ->scalar();
        $num = ceil($max_id / 10000);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            for ($i = 1; $i <= $num; $i++) {
                $start_limit = ($i - 1) * 10000;
                $end_limit = $i * 10000;

                echo "start--".round((memory_get_usage()/1024/1024), 2),"M...memory \r\n";
                $this->actionLoanInsert($start_limit, $end_limit);
                echo "end--".round((memory_get_usage()/1024/1024), 2),"M...memory \r\n";
            }

            $transaction->commit();
            die('执行成功');
        } catch (\Exception $e) {
            $transaction->rollBack();
            die('执行失败：'.$e->getMessage());
        }
    }

    /**
     * 一次性把数据刷入post_channel_repay
     *
     */
    public function actionAllImportRepay()
    {
        $max_id = Asset::find()
            ->select(['asset_id'])
            ->orderBy(['asset_id' => SORT_DESC])
            ->limit(1)
            ->asArray()
            ->scalar();
        $num = ceil($max_id / 10000);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            for ($i = 1; $i <= $num; $i++) {
                $start_limit = ($i - 1) * 10000;
                $end_limit = $i * 10000;

                echo "start--".round((memory_get_usage()/1024/1024), 2),"M...memory \r\n";
                $this->actionRepayInsert($start_limit, $end_limit);
                echo "end--".round((memory_get_usage()/1024/1024), 2),"M...memory \r\n";
            }

            $transaction->commit();
            die('执行成功');
        } catch (\Exception $e) {
            $transaction->rollBack();
            die('执行失败：'.$e->getMessage());
        }
    }

    private function actionLoanInsert($start_limit, $end_limit)
    {
        $now = Dh::getcurrentDateTime();
        $sql1 = "select grant_finish_at,asset_id,asset_type,asset_sub_type,asset_period_count,asset_period_days,grant_channel,asset_from_system,
                 grant_creditor,asset_principal_amount,asset_principal_amount*0.07,0.07,'$now' from asset left join `grant` on grant_asset_id = asset_id
                 where (`asset_id` > $start_limit) AND (`asset_id` <= $end_limit)";

        $sql = "INSERT INTO post_channel_loan (post_channel_loan_date,post_channel_loan_asset_id,post_channel_loan_asset_type,post_channel_loan_asset_sub_type,post_channel_loan_asset_period_count,post_channel_loan_asset_period_days,post_channel_loan_asset_loan_channel,
                post_channel_loan_asset_from_system,post_channel_loan_asset_fund_provider,post_channel_loan_principal_amount,post_channel_loan_allowance_amount,post_channel_loan_allowance_rate,post_channel_loan_create_at) $sql1";

        Yii::$app->db->createCommand($sql)->execute();
    }

    private function actionRepayInsert($start_limit, $end_limit)
    {
        $now = Dh::getcurrentDateTime();

        //提前还款的数据
        $sql1 = "select a.repay_detail_finish_at,a.asset_id,a.asset_type,a.asset_sub_type,a.asset_period_count,a.asset_period_days,a.grant_channel,
        a.asset_from_system,a.grant_creditor,a.sum_principal,a.sum_interest,ifnull(b.sum_service_amount,0) as sum_service_amount,0,'advance',
        ifnull(c.sum_manage_amount,0) as sum_manage_amount,a.num,is_day,'$now'
        from (
        (select asset_id,repay_detail_finish_at,asset_type,asset_sub_type,asset_period_count,asset_period_days,grant_channel,asset_from_system,grant_creditor,count(1) as num,
        sum(if(repay_detail_repay_type = 'repay',repay_detail_principal_amount,0)) - sum(if(repay_detail_repay_type = 'reverse',repay_detail_principal_amount,0)) as sum_principal,
        sum(if(repay_detail_repay_type = 'repay',repay_detail_interest_amount,0)) - sum(if(repay_detail_repay_type = 'reverse',repay_detail_interest_amount,0)) as sum_interest,
        sum(if(repay_detail_repay_type = 'repay',repay_detail_decrease_amount,0)) - sum(if(repay_detail_repay_type = 'reverse',repay_detail_decrease_amount,0)) as sum_decrease,
        datediff(repay_detail_finish_at,repay_expect_finish_at) as is_day
        from asset
        left join `grant` on grant_asset_id = asset_id
        left join repay on repay_asset_id = asset_id
        left join repay_detail on repay_detail_repay_id = repay_id
        where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and repay_detail_repay_type in ('repay','reverse') and datediff(repay_detail_finish_at,repay_expect_finish_at) < 0 group by asset_id) as a
        left join
        (select asset_id,fee_repay_detail_finish_at,
        sum(if(fee_repay_detail_repay_type = 'repay',fee_repay_detail_amount,0)) - sum(if(fee_repay_detail_repay_type = 'reverse',fee_repay_detail_amount,0)) as sum_service_amount,
        datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) as f_day
        from asset
        left join fee on fee_asset_id = asset_id
        left join fee_repay on fee_repay_fee_id = fee_id
        left join fee_repay_detail on fee_repay_detail_fee_repay_id = fee_repay_id
        where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and fee_repay_detail_repay_type in ('repay','reverse') and datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) < 0 group by asset_id) as b
        on a.asset_id = b.asset_id
        left join
        (select asset_id,
        sum(if(fee_repay_detail_repay_type = 'repay',fee_repay_detail_amount,0)) - sum(if(fee_repay_detail_repay_type = 'reverse',fee_repay_detail_amount,0)) as sum_manage_amount,
        datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) as f_day
        from asset
        left join fee on fee_asset_id = asset_id and fee_type = 'manage'
        left join fee_repay on fee_repay_fee_id = fee_id
        left join fee_repay_detail on fee_repay_detail_fee_repay_id = fee_repay_id
        where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and fee_repay_detail_repay_type in ('repay','reverse') and datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) < 0 group by asset_id) as c
        on a.asset_id = c.asset_id )
        where (a.asset_id > $start_limit) AND (a.asset_id <= $end_limit)";

        //正常还款的数据
        $sql2 = "select a.repay_detail_finish_at,a.asset_id,a.asset_type,a.asset_sub_type,a.asset_period_count,a.asset_period_days,a.grant_channel,
        a.asset_from_system,a.grant_creditor,a.sum_principal,a.sum_interest,ifnull(b.sum_service_amount,0) as sum_service_amount,0,'normal',
        ifnull(c.sum_manage_amount,0) as sum_manage_amount,a.num,is_day,'$now'
        from (
        (select asset_id,repay_detail_finish_at,asset_type,asset_sub_type,asset_period_count,asset_period_days,grant_channel,asset_from_system,grant_creditor,count(1) as num,
        sum(if(repay_detail_repay_type = 'repay',repay_detail_principal_amount,0)) - sum(if(repay_detail_repay_type = 'reverse',repay_detail_principal_amount,0)) as sum_principal,
        sum(if(repay_detail_repay_type = 'repay',repay_detail_interest_amount,0)) - sum(if(repay_detail_repay_type = 'reverse',repay_detail_interest_amount,0)) as sum_interest,
        sum(if(repay_detail_repay_type = 'repay',repay_detail_decrease_amount,0)) - sum(if(repay_detail_repay_type = 'reverse',repay_detail_decrease_amount,0)) as sum_decrease,
        datediff(repay_detail_finish_at,repay_expect_finish_at) as is_day
        from asset
        left join `grant` on grant_asset_id = asset_id
        left join repay on repay_asset_id = asset_id
        left join repay_detail on repay_detail_repay_id = repay_id 
        where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and repay_detail_repay_type in ('repay','reverse') and datediff(repay_detail_finish_at,repay_expect_finish_at) = 0 group by asset_id) as a
        left join
        (select asset_id,fee_repay_detail_finish_at,
        sum(if(fee_repay_detail_repay_type = 'repay',fee_repay_detail_amount,0)) - sum(if(fee_repay_detail_repay_type = 'reverse',fee_repay_detail_amount,0)) as sum_service_amount,
        datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) as f_day
        from asset
        left join fee on fee_asset_id = asset_id
        left join fee_repay on fee_repay_fee_id = fee_id
        left join fee_repay_detail on fee_repay_detail_fee_repay_id = fee_repay_id
        where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and fee_repay_detail_repay_type in ('repay','reverse') and datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) = 0 group by asset_id) as b
        on a.asset_id = b.asset_id
        left join
        (select asset_id,
        sum(if(fee_repay_detail_repay_type = 'repay',fee_repay_detail_amount,0)) - sum(if(fee_repay_detail_repay_type = 'reverse',fee_repay_detail_amount,0)) as sum_manage_amount,
        datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) as f_day
        from asset
        left join fee on fee_asset_id = asset_id and fee_type = 'manage'
        left join fee_repay on fee_repay_fee_id = fee_id
        left join fee_repay_detail on fee_repay_detail_fee_repay_id = fee_repay_id
        where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and fee_repay_detail_repay_type in ('repay','reverse') and datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) = 0 group by asset_id) as c
        on a.asset_id = c.asset_id )
        where (a.asset_id > $start_limit) AND (a.asset_id <= $end_limit)";

        //逾期还款的数据
        $sql3 = "select a.repay_detail_finish_at,a.asset_id,a.asset_type,a.asset_sub_type,a.asset_period_count,a.asset_period_days,a.grant_channel,
        a.asset_from_system,a.grant_creditor,a.sum_principal,a.sum_interest,ifnull(b.sum_service_amount,0) as sum_service_amount,
        ifnull(d.sum_late_amount,0) as sum_late_amount,'compensation',ifnull(c.sum_manage_amount,0) as sum_manage_amount,a.num,is_day,'$now'
        from (
        (select asset_id,repay_detail_finish_at,asset_type,asset_sub_type,asset_period_count,asset_period_days,grant_channel,asset_from_system,grant_creditor,count(1) as num,  
        sum(if(repay_detail_repay_type = 'repay',repay_detail_principal_amount,0)) - sum(if(repay_detail_repay_type = 'reverse',repay_detail_principal_amount,0)) as sum_principal,
        sum(if(repay_detail_repay_type = 'repay',repay_detail_interest_amount,0)) - sum(if(repay_detail_repay_type = 'reverse',repay_detail_interest_amount,0)) as sum_interest,
        sum(if(repay_detail_repay_type = 'repay',repay_detail_decrease_amount,0)) - sum(if(repay_detail_repay_type = 'reverse',repay_detail_decrease_amount,0)) as sum_decrease,  
        datediff(repay_detail_finish_at,repay_expect_finish_at) as is_day
        from asset
        left join `grant` on grant_asset_id = asset_id
        left join repay on repay_asset_id = asset_id
        left join repay_detail on repay_detail_repay_id = repay_id
        where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and repay_detail_repay_type in ('repay','reverse') and datediff(repay_detail_finish_at,repay_expect_finish_at) > 0 group by asset_id) as a
        left join
        (select asset_id,fee_repay_detail_finish_at,  
        sum(if(fee_repay_detail_repay_type = 'repay',fee_repay_detail_amount,0)) - sum(if(fee_repay_detail_repay_type = 'reverse',fee_repay_detail_amount,0)) as sum_service_amount,
        datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) as f_day
        from asset
        left join fee on fee_asset_id = asset_id
        left join fee_repay on fee_repay_fee_id = fee_id
        left join fee_repay_detail on fee_repay_detail_fee_repay_id = fee_repay_id
        where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and fee_repay_detail_repay_type in ('repay','reverse') and datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) > 0 group by asset_id) as b
        on a.asset_id = b.asset_id
        left join
        (select asset_id,  
        sum(if(fee_repay_detail_repay_type = 'repay',fee_repay_detail_amount,0)) - sum(if(fee_repay_detail_repay_type = 'reverse',fee_repay_detail_amount,0)) as sum_manage_amount,
        datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) as f_day
        from asset
        left join fee on fee_asset_id = asset_id and fee_type = 'manage'
        left join fee_repay on fee_repay_fee_id = fee_id
        left join fee_repay_detail on fee_repay_detail_fee_repay_id = fee_repay_id
        where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and fee_repay_detail_repay_type in ('repay','reverse') and datediff(fee_repay_detail_finish_at,fee_repay_expect_finish_at) > 0 group by asset_id) as c
        on a.asset_id = c.asset_id 
        left join
        (select asset_id,late_fee_repay_detail_finish_at,  
        sum(if(late_fee_repay_detail_repay_type = 'repay',late_fee_repay_detail_amount,0)) - sum(if(late_fee_repay_detail_repay_type = 'reverse',late_fee_repay_detail_amount,0)) as sum_late_amount
        from asset
        left join late_fee on late_fee_asset_id = asset_id
        left join late_fee_repay on late_fee_repay_fee_id = late_fee_id
        left join late_fee_repay_detail on late_fee_repay_detail_fee_repay_id = late_fee_repay_id
        where asset_charge_type = 1 and asset_type = 'paydayloan' and asset_sub_type = 'single' and late_fee_repay_detail_repay_type in ('repay','reverse') group by asset_id) as d
        on a.asset_id = d.asset_id )
        where (a.asset_id > $start_limit) AND (a.asset_id <= $end_limit)";

        $sqlStr1 = "INSERT INTO post_channel_repay (post_channel_repay_date,post_channel_repay_asset_id,post_channel_repay_asset_type,post_channel_repay_asset_sub_type,post_channel_repay_asset_period_count,post_channel_repay_asset_period_days,
  post_channel_repay_asset_loan_channel,post_channel_repay_asset_from_system,post_channel_repay_asset_fund_provider,post_channel_repay_principal_amount,
  post_channel_repay_interest_amount,post_channel_repay_service_amount,post_channel_repay_late_amount,post_channel_repay_type,post_channel_repay_trial_amount,
  post_channel_repay_quantity,post_channel_repay_late_day,post_channel_repay_create_at) $sql1";

        $sqlStr2 = "INSERT INTO post_channel_repay (post_channel_repay_date,post_channel_repay_asset_id,post_channel_repay_asset_type,post_channel_repay_asset_sub_type,post_channel_repay_asset_period_count,post_channel_repay_asset_period_days,
  post_channel_repay_asset_loan_channel,post_channel_repay_asset_from_system,post_channel_repay_asset_fund_provider,post_channel_repay_principal_amount,
  post_channel_repay_interest_amount,post_channel_repay_service_amount,post_channel_repay_late_amount,post_channel_repay_type,post_channel_repay_trial_amount,
  post_channel_repay_quantity,post_channel_repay_late_day,post_channel_repay_create_at) $sql2";

        $sqlStr3 = "INSERT INTO post_channel_repay (post_channel_repay_date,post_channel_repay_asset_id,post_channel_repay_asset_type,post_channel_repay_asset_sub_type,post_channel_repay_asset_period_count,post_channel_repay_asset_period_days,
  post_channel_repay_asset_loan_channel,post_channel_repay_asset_from_system,post_channel_repay_asset_fund_provider,post_channel_repay_principal_amount,
  post_channel_repay_interest_amount,post_channel_repay_service_amount,post_channel_repay_late_amount,post_channel_repay_type,post_channel_repay_trial_amount,
  post_channel_repay_quantity,post_channel_repay_late_day,post_channel_repay_create_at) $sql3";

        Yii::$app->db->createCommand($sqlStr1)->execute();
        Yii::$app->db->createCommand($sqlStr2)->execute();
        Yii::$app->db->createCommand($sqlStr3)->execute();
    }

    /**
     * 把数据刷入统计表statistics_repay
     *
     */
    public function actionAddStatisticsRepay()
    {
        $argv = func_get_args();
        $yesterday = Dh::yesterdayDate();
        $now = Dh::getcurrentDateTime();
        $whereTime = isset($argv[0]) && !empty($argv[0]) ? $argv[0] : $yesterday;

        $sqlSelect = "select * from statistics_repay where statistics_repay_date = '$whereTime'";
        $res = Yii::$app->db->createCommand($sqlSelect)->queryAll();
        if (!empty($res)) {
            die('数据已经导入过，不能重复执行！');
        }

        $sql = "select * from post_channel_repay where post_channel_repay_date = '$whereTime'";
        $res = Yii::$app->db->createCommand($sql)->queryAll();
        if (empty($res)) {
            die('post_channel_repay表基础数据不存在');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            //提前还款
            $params['start_time']  = $whereTime;
            $params['end_time']    = $whereTime;
            $params['status_type'] = 'advance';

            $searchModel = new PostChannelRepay();
            $data        = $searchModel->repayStatisticsByChannel($params);
            $data        = array_map(function ($item) {
                $cate     = RateConfig::getValue($item['post_channel_repay_asset_loan_channel'], $item['post_channel_repay_asset_period_days'], $item['post_channel_repay_date'], 1);
                $feeSplit = PostOverdueCompensation::splitCalculator($item['sum_repay_principal_amount'], $item['post_channel_repay_asset_period_days'], $cate);

                //保理费(争时才有)
                if ($item['post_channel_repay_asset_loan_channel'] == Consts::ASSET_LOAN_CHANNEL_CREDITOR_ZHENGSHI) {
                    $item['factoring_amount'] = $item['sum_repay_principal_amount'] * $cate->factoring_rate;
                } else {
                    $item['factoring_amount'] = 0;
                }
                $item['information_amount']      = $feeSplit['information_amount'];
                $item['late_amount']             = $feeSplit['reserves_amount'];
                $item['capital_service_amount']  = $feeSplit['platform_amount'];
                $item['capital_interest_amount'] = $feeSplit['investor_amount'];
                $item['technology_amount']       = $item['sum_all_repay_amount'] - $item['sum_repay_principal_amount'] - $item['information_amount']
                    - $item['late_amount'] - $item['capital_service_amount'] - $item['capital_interest_amount']
                    - $item['sum_repay_trial_amount'] - $item['factoring_amount'];

                return $item;
            }, $data);

            $dataAllen = $searchModel->repayStatisticsByAllen($params);
            $dataFinal = $this->processAllenData($dataAllen);

            $resultArr = array_merge($data, $dataFinal);
            $statisticsData = array_map(function($item) use($now){
                $new_item = [
                    'statistics_repay_date'                    => $item['post_channel_repay_date'],
                    'statistics_repay_period_days'             => $item['post_channel_repay_asset_period_days'],
                    'statistics_repay_loan_channel'            => $item['post_channel_repay_asset_loan_channel'],
                    'statistics_repay_type'                    => 'advance',
                    'statistics_repay_all_repay_amount'        => $item['sum_all_repay_amount'],
                    'statistics_repay_principal_amount'        => $item['sum_repay_principal_amount'],
                    'statistics_repay_technology_amount'       => $item['technology_amount'],
                    'statistics_repay_information_amount'      => $item['information_amount'],
                    'statistics_repay_late_amount'             => $item['late_amount'],
                    'statistics_repay_factoring_amount'        => $item['factoring_amount'],
                    'statistics_repay_capital_service_amount'  => $item['capital_service_amount'],
                    'statistics_repay_capital_interest_amount' => $item['capital_interest_amount'],
                    'statistics_repay_trial_amount'            => $item['sum_repay_trial_amount'],
                    'statistics_repay_use_trial_amount'        => $item['sum_use_trial_amount'],
                    'statistics_repay_create_at'               => $now,
                ];
                return $new_item;
            }, $resultArr);

            Yii::$app->db->createCommand()->batchInsert('statistics_repay', [
                'statistics_repay_date',
                'statistics_repay_period_days',
                'statistics_repay_loan_channel',
                'statistics_repay_type',
                'statistics_repay_all_repay_amount',
                'statistics_repay_principal_amount',
                'statistics_repay_technology_amount',
                'statistics_repay_information_amount',
                'statistics_repay_late_amount',
                'statistics_repay_factoring_amount',
                'statistics_repay_capital_service_amount',
                'statistics_repay_capital_interest_amount',
                'statistics_repay_trial_amount',
                'statistics_repay_use_trial_amount',
                'statistics_repay_create_at',
            ], $statisticsData)->execute();

            //正常还款
            $params['start_time']  = $whereTime;
            $params['end_time']    = $whereTime;
            $params['status_type'] = 'normal';

            $searchModel = new PostChannelRepay();
            $data        = $searchModel->repayStatisticsByChannel($params);
            $data        = array_map(function ($item) {
                $cate     = RateConfig::getValue($item['post_channel_repay_asset_loan_channel'], $item['post_channel_repay_asset_period_days'], $item['post_channel_repay_date'], 1);
                $feeSplit = PostOverdueCompensation::splitCalculator($item['sum_repay_principal_amount'], $item['post_channel_repay_asset_period_days'], $cate);

                //保理费(争时才有)
                if ($item['post_channel_repay_asset_loan_channel'] == Consts::ASSET_LOAN_CHANNEL_CREDITOR_ZHENGSHI) {
                    $item['factoring_amount'] = $item['sum_repay_principal_amount'] * $cate->factoring_rate;
                } else {
                    $item['factoring_amount'] = 0;
                }
                $item['information_amount']      = $feeSplit['information_amount'];
                $item['late_amount']             = $feeSplit['reserves_amount'];
                $item['capital_service_amount']  = $feeSplit['platform_amount'];
                $item['capital_interest_amount'] = $feeSplit['investor_amount'];
                $item['technology_amount']       = $item['sum_all_repay_amount'] - $item['sum_repay_principal_amount'] - $item['information_amount']
                    - $item['late_amount'] - $item['capital_service_amount'] - $item['capital_interest_amount']
                    - $item['sum_repay_trial_amount'] - $item['factoring_amount'];

                return $item;
            }, $data);

            $dataAllen = $searchModel->repayStatisticsByAllen($params);
            $dataFinal = $this->processAllenData($dataAllen);

            $resultArr = array_merge($data, $dataFinal);
            $statisticsData = array_map(function($item) use($now){
                $new_item = [
                    'statistics_repay_date'                    => $item['post_channel_repay_date'],
                    'statistics_repay_period_days'             => $item['post_channel_repay_asset_period_days'],
                    'statistics_repay_loan_channel'            => $item['post_channel_repay_asset_loan_channel'],
                    'statistics_repay_type'                    => 'normal',
                    'statistics_repay_all_repay_amount'        => $item['sum_all_repay_amount'],
                    'statistics_repay_principal_amount'        => $item['sum_repay_principal_amount'],
                    'statistics_repay_technology_amount'       => $item['technology_amount'],
                    'statistics_repay_information_amount'      => $item['information_amount'],
                    'statistics_repay_late_amount'             => $item['late_amount'],
                    'statistics_repay_factoring_amount'        => $item['factoring_amount'],
                    'statistics_repay_capital_service_amount'  => $item['capital_service_amount'],
                    'statistics_repay_capital_interest_amount' => $item['capital_interest_amount'],
                    'statistics_repay_trial_amount'            => $item['sum_repay_trial_amount'],
                    'statistics_repay_use_trial_amount'        => $item['sum_use_trial_amount'],
                    'statistics_repay_create_at'               => $now,
                ];
                return $new_item;
            }, $resultArr);

            Yii::$app->db->createCommand()->batchInsert('statistics_repay', [
                'statistics_repay_date',
                'statistics_repay_period_days',
                'statistics_repay_loan_channel',
                'statistics_repay_type',
                'statistics_repay_all_repay_amount',
                'statistics_repay_principal_amount',
                'statistics_repay_technology_amount',
                'statistics_repay_information_amount',
                'statistics_repay_late_amount',
                'statistics_repay_factoring_amount',
                'statistics_repay_capital_service_amount',
                'statistics_repay_capital_interest_amount',
                'statistics_repay_trial_amount',
                'statistics_repay_use_trial_amount',
                'statistics_repay_create_at',
            ], $statisticsData)->execute();

            //逾期还款
            $params['start_time']  = $whereTime;
            $params['end_time']    = $whereTime;
            $params['status_type'] = 'compensation';

            $searchModel = new PostChannelRepay();
            $data        = $searchModel->repayStatisticsByChannel($params);
            $data        = array_map(function ($item) {
                $cate = RateConfig::getValue($item['post_channel_repay_asset_loan_channel'], $item['post_channel_repay_asset_period_days'], $item['post_channel_repay_date'], 1);
                $feeSplit = PostOverdueCompensation::splitCalculator($item['before_sum_repay_principal_amount'], $item['post_channel_repay_asset_period_days'], $cate);

                $item['risk_amount']   = $item['sum_late_amount'];
                //代收逾期利息(仅钱牛牛存在)
                if ($item['post_channel_repay_asset_loan_channel'] == 'hengfeng') {
                    $item['collection_late_interest'] = round($item['sum_late_interest'] * 0.1 / 360, 2);
                    $item['sum_repay_principal_amount'] = $item['before_sum_repay_principal_amount'];
                    $item['sum_repay_trial_amount']     = $item['before_sum_repay_trial_amount'];
                    $item['information_amount']         = $feeSplit['information_amount'];
                    $item['late_amount']                = $feeSplit['reserves_amount'];
                    $item['capital_service_amount']     = $feeSplit['platform_amount'];
                    $item['capital_interest_amount']    = $feeSplit['investor_amount'];
                    $item['before_should_amount']       = $item['before_sum_repay_trial_amount'] + $item['before_sum_repay_principal_amount'] + $item['before_sum_repay_principal_amount'] * $cate->withhold_rate;
                    $item['after_should_amount']        = $item['after_sum_repay_trial_amount'] + $item['after_sum_repay_principal_amount'] + $item['after_sum_repay_principal_amount'] * $cate->withhold_rate;
                    $item['technology_amount']          = $item['sum_all_repay_amount'] - $item['sum_repay_principal_amount'] - $item['information_amount']
                        - $item['late_amount'] - $item['capital_service_amount'] - $item['capital_interest_amount']
                        - $item['sum_repay_trial_amount'] - $item['risk_amount'] - $item['after_should_amount'];
                } else {
                    $item['collection_late_interest']   = 0;
                    $item['information_amount']         = 0;
                    $item['late_amount']                = 0;
                    $item['capital_service_amount']     = 0;
                    $item['capital_interest_amount']    = 0;
                    $item['technology_amount']          = 0;
                    $item['before_should_amount']       = 0;
                    $item['after_should_amount']        = $item['sum_repay_trial_amount'] + $item['sum_repay_principal_amount'] + $item['sum_repay_principal_amount'] * $cate->withhold_rate;
                    $item['sum_repay_principal_amount'] = 0;
                    $item['sum_repay_trial_amount']     = 0;
                }

                return $item;
            }, $data);

            $dataAllen = $searchModel->repayStatisticsByAllen($params);
            $dataFinal = $this->processLateAllenData($dataAllen);

            $resultArr = array_merge($data, $dataFinal);
            $statisticsData = array_map(function($item) use($now){
                $new_item = [
                    'statistics_repay_date'                     => $item['post_channel_repay_date'],
                    'statistics_repay_period_days'              => $item['post_channel_repay_asset_period_days'],
                    'statistics_repay_loan_channel'             => $item['post_channel_repay_asset_loan_channel'],
                    'statistics_repay_type'                     => 'compensation',
                    'statistics_repay_all_repay_amount'         => $item['sum_all_repay_amount'],
                    'statistics_repay_principal_amount'         => $item['sum_repay_principal_amount'],
                    'statistics_repay_technology_amount'        => $item['technology_amount'],
                    'statistics_repay_information_amount'       => $item['information_amount'],
                    'statistics_repay_late_amount'              => $item['late_amount'],
                    'statistics_repay_factoring_amount'         => 0,
                    'statistics_repay_capital_service_amount'   => $item['capital_service_amount'],
                    'statistics_repay_capital_interest_amount'  => $item['capital_interest_amount'],
                    'statistics_repay_trial_amount'             => $item['sum_repay_trial_amount'],
                    'statistics_repay_use_trial_amount'         => 0,
                    'statistics_repay_risk_amount'              => $item['risk_amount'],
                    'statistics_repay_before_should_amount'     => $item['before_should_amount'],
                    'statistics_repay_collection_late_interest' => $item['collection_late_interest'],
                    'statistics_repay_after_should_amount'      => $item['after_should_amount'],
                    'statistics_repay_create_at'                => $now,
                ];
                return $new_item;
            }, $resultArr);

            Yii::$app->db->createCommand()->batchInsert('statistics_repay', [
                'statistics_repay_date',
                'statistics_repay_period_days',
                'statistics_repay_loan_channel',
                'statistics_repay_type',
                'statistics_repay_all_repay_amount',
                'statistics_repay_principal_amount',
                'statistics_repay_technology_amount',
                'statistics_repay_information_amount',
                'statistics_repay_late_amount',
                'statistics_repay_factoring_amount',
                'statistics_repay_capital_service_amount',
                'statistics_repay_capital_interest_amount',
                'statistics_repay_trial_amount',
                'statistics_repay_use_trial_amount',
                'statistics_repay_risk_amount',
                'statistics_repay_before_should_amount',
                'statistics_repay_collection_late_interest',
                'statistics_repay_after_should_amount',
                'statistics_repay_create_at',
            ], $statisticsData)->execute();

            $transaction->commit();
            die('执行成功');
        } catch (\Exception $e) {
            $transaction->rollBack();
            die('执行失败：'.$e->getMessage());
        }
    }

    /**
     * 把数据刷入统计表statistics_loan
     *
     */
    public function actionAddStatisticsLoan()
    {
        $argv = func_get_args();
        $yesterday = Dh::yesterdayDate();
        $now = Dh::getcurrentDateTime();
        $whereTime = isset($argv[0]) && !empty($argv[0]) ? $argv[0] : $yesterday;

        $sqlSelect = "select * from statistics_loan where statistics_loan_date = '$whereTime'";
        $res = Yii::$app->db->createCommand($sqlSelect)->queryAll();
        if (!empty($res)) {
            die('数据已经导入过，不能重复执行！');
        }

        $sql = "select * from post_channel_loan where post_channel_loan_date = '$whereTime'";
        $res = Yii::$app->db->createCommand($sql)->queryAll();
        if (empty($res)) {
            die('post_channel_loan表基础数据不存在');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $params['start_time'] = $whereTime;
            $params['end_time']   = $whereTime;


            $searchModel = new PostChannelLoan();
            $data        = $searchModel->loanStatisticsByChannel($params);
            $dataAllen   = $searchModel->loanStatisticsByAllen($params);
            $dataAllen   = array_map(function ($item) {
                $item['post_channel_loan_asset_loan_channel'] = 'allen';

                return $item;
            }, $dataAllen);

            $resultArr = array_merge($data, $dataAllen);
            $statisticsData = array_map(function($item) use($now){
                $new_item = [
                    'statistics_loan_date'        => $item['post_channel_loan_date'],
                    'statistics_loan_period_days' => $item['post_channel_loan_asset_period_days'],
                    'statistics_loan_channel'     => $item['post_channel_loan_asset_loan_channel'],
                    'statistics_loan_principal'   => $item['sum_principal'],
                    'statistics_loan_allowance'   => $item['sum_allowance'],
                    'statistics_loan_create_at'   => $now,
                ];
                return $new_item;
            }, $resultArr);

            Yii::$app->db->createCommand()->batchInsert('statistics_loan', [
                'statistics_loan_date',
                'statistics_loan_period_days',
                'statistics_loan_channel',
                'statistics_loan_principal',
                'statistics_loan_allowance',
                'statistics_loan_create_at',
            ], $statisticsData)->execute();

            $transaction->commit();
            die('执行成功');
        } catch (\Exception $e) {
            $transaction->rollBack();
            die('执行失败：'.$e->getMessage());
        }
    }

    protected function processAllenData($dataAllen)
    {
        $dateArr = array_map(function ($item) {
            return $item['post_channel_repay_date'];
        }, $dataAllen);
        $dateArr = array_unique($dateArr);

        $dataAllen = array_map(function ($item) {
            $cate     = RateConfig::getValue($item['post_channel_repay_asset_loan_channel'], $item['post_channel_repay_asset_period_days'], $item['post_channel_repay_date'], 1);
            $feeSplit = PostOverdueCompensation::splitCalculator($item['sum_repay_principal_amount'], $item['post_channel_repay_asset_period_days'], $cate);

            //保理费(争时才有) allen都是零
            $item['factoring_amount']        = 0;
            $item['information_amount']      = $feeSplit['information_amount'];
            $item['late_amount']             = $feeSplit['reserves_amount'];
            $item['capital_service_amount']  = $feeSplit['platform_amount'];
            $item['capital_interest_amount'] = $feeSplit['investor_amount'];
            $item['technology_amount']       = $item['sum_all_repay_amount'] - $item['sum_repay_principal_amount'] - $item['information_amount']
                - $item['late_amount'] - $item['capital_service_amount'] - $item['capital_interest_amount']
                - $item['sum_repay_trial_amount'] - $item['factoring_amount'];

            return $item;
        }, $dataAllen);

        $dataRes = [];
        foreach ($dataAllen as $val) {
            foreach ($dateArr as $date) {
                if ($val['post_channel_repay_asset_period_days'] == 7 && $val['post_channel_repay_date'] == $date) {
                    $dataRes[$date][7][] = $val;
                } else {
                    if ($val['post_channel_repay_asset_period_days'] == 14 && $val['post_channel_repay_date'] == $date) {
                        $dataRes[$date][14][] = $val;
                    } else {
                        if ($val['post_channel_repay_asset_period_days'] == 30 && $val['post_channel_repay_date'] == $date) {
                            $dataRes[$date][30][] = $val;
                        }
                    }
                }
            }
        }

        $dataFinal = [];
        foreach ($dataRes as $k => $item) {
            foreach ($item as $val) {
                $factoring_amount = $technology_amount = $information_amount = $late_amount = $capital_service_amount = $capital_interest_amount = 0;
                $sum_repay_principal_amount = $sum_repay_trial_amount = $sum_use_trial_amount = $sum_all_repay_amount = 0;

                foreach ($val as $v) {
                    $sum_repay_principal_amount            += $v['sum_repay_principal_amount'];
                    $sum_repay_trial_amount                += $v['sum_repay_trial_amount'];
                    $sum_use_trial_amount                  += $v['sum_use_trial_amount'];
                    $sum_all_repay_amount                  += $v['sum_all_repay_amount'];
                    $technology_amount                     += $v['technology_amount'];
                    $information_amount                    += $v['information_amount'];
                    $late_amount                           += $v['late_amount'];
                    $capital_service_amount                += $v['capital_service_amount'];
                    $capital_interest_amount               += $v['capital_interest_amount'];
                    $factoring_amount                      += $v['factoring_amount'];
                    $post_channel_repay_date               = $v['post_channel_repay_date'];
                    $post_channel_repay_asset_loan_channel = 'allen';
                    $post_channel_repay_asset_period_days  = $v['post_channel_repay_asset_period_days'];
                }
                $dataFinal[] = [
                    'post_channel_repay_date'               => $post_channel_repay_date,
                    'post_channel_repay_asset_loan_channel' => $post_channel_repay_asset_loan_channel,
                    'post_channel_repay_asset_period_days'  => $post_channel_repay_asset_period_days,
                    'sum_repay_principal_amount'            => $sum_repay_principal_amount,
                    'sum_repay_trial_amount'                => $sum_repay_trial_amount,
                    'sum_use_trial_amount'                  => $sum_use_trial_amount,
                    'sum_all_repay_amount'                  => $sum_all_repay_amount,
                    'technology_amount'                     => $technology_amount,
                    'information_amount'                    => $information_amount,
                    'late_amount'                           => $late_amount,
                    'capital_service_amount'                => $capital_service_amount,
                    'capital_interest_amount'               => $capital_interest_amount,
                    'factoring_amount'                      => $factoring_amount,
                ];
            }
        }

        return $dataFinal;
    }

    protected function processLateAllenData($dataAllen)
    {
        $dateArr = array_map(function ($item) {
            return $item['post_channel_repay_date'];
        }, $dataAllen);
        $dateArr = array_unique($dateArr);

        $dataAllen = array_map(function ($item) {
            $cate = RateConfig::getValue($item['post_channel_repay_asset_loan_channel'], $item['post_channel_repay_asset_period_days'], $item['post_channel_repay_date'], 1);
            $feeSplit = PostOverdueCompensation::splitCalculator($item['before_sum_repay_principal_amount'], $item['post_channel_repay_asset_period_days'], $cate);

            $item['risk_amount']   = $item['sum_late_amount'];
            //代收逾期利息(仅钱牛牛存在)
            if ($item['post_channel_repay_asset_loan_channel'] == 'hengfeng') {
                $item['collection_late_interest'] = round($item['sum_late_interest'] * 0.1 / 360, 2);
                $item['sum_repay_principal_amount'] = $item['before_sum_repay_principal_amount'];
                $item['sum_repay_trial_amount']     = $item['before_sum_repay_trial_amount'];
                $item['information_amount']         = $feeSplit['information_amount'];
                $item['late_amount']                = $feeSplit['reserves_amount'];
                $item['capital_service_amount']     = $feeSplit['platform_amount'];
                $item['capital_interest_amount']    = $feeSplit['investor_amount'];
                $item['before_should_amount']       = $item['before_sum_repay_trial_amount'] + $item['before_sum_repay_principal_amount'] + $item['before_sum_repay_principal_amount'] * $cate->withhold_rate;
                $item['after_should_amount']        = $item['after_sum_repay_trial_amount'] + $item['after_sum_repay_principal_amount'] + $item['after_sum_repay_principal_amount'] * $cate->withhold_rate;
                $item['technology_amount']          = $item['sum_all_repay_amount'] - $item['sum_repay_principal_amount'] - $item['information_amount']
                    - $item['late_amount'] - $item['capital_service_amount'] - $item['capital_interest_amount']
                    - $item['sum_repay_trial_amount'] - $item['risk_amount'] - $item['after_should_amount'];
            } else {
                $item['collection_late_interest']   = 0;
                $item['information_amount']         = 0;
                $item['late_amount']                = 0;
                $item['capital_service_amount']     = 0;
                $item['capital_interest_amount']    = 0;
                $item['technology_amount']          = 0;
                $item['before_should_amount']       = 0;
                $item['after_should_amount']        = $item['sum_repay_trial_amount'] + $item['sum_repay_principal_amount'] + $item['sum_repay_principal_amount'] * $cate->withhold_rate;
                $item['sum_repay_principal_amount'] = 0;
                $item['sum_repay_trial_amount']     = 0;
            }

            return $item;
        }, $dataAllen);

        $dataRes = [];
        foreach ($dataAllen as $val) {
            foreach ($dateArr as $date) {
                if ($val['post_channel_repay_asset_period_days'] == 7 && $val['post_channel_repay_date'] == $date) {
                    $dataRes[$date][7][] = $val;
                } else {
                    if ($val['post_channel_repay_asset_period_days'] == 14 && $val['post_channel_repay_date'] == $date) {
                        $dataRes[$date][14][] = $val;
                    } else {
                        if ($val['post_channel_repay_asset_period_days'] == 30 && $val['post_channel_repay_date'] == $date) {
                            $dataRes[$date][30][] = $val;
                        }
                    }
                }
            }
        }

        $dataFinal = [];
        foreach ($dataRes as $k => $item) {
            foreach ($item as $val) {
                $sum_all_repay_amount = $risk_amount = $before_should_amount = $after_should_amount = $collection_late_interest = 0;
                $technology_amount = $information_amount = $late_amount = $capital_service_amount = $capital_interest_amount = 0;
                $sum_repay_principal_amount = $sum_repay_trial_amount = 0;

                foreach ($val as $v) {
                    $sum_all_repay_amount       += $v['sum_all_repay_amount'];
                    $risk_amount                += $v['risk_amount'];
                    $before_should_amount       += $v['before_should_amount'];
                    $after_should_amount        += $v['after_should_amount'];
                    $collection_late_interest   += $v['collection_late_interest'];
                    $technology_amount          += $v['technology_amount'];
                    $information_amount         += $v['information_amount'];
                    $late_amount                += $v['late_amount'];
                    $capital_service_amount     += $v['capital_service_amount'];
                    $capital_interest_amount    += $v['capital_interest_amount'];
                    $sum_repay_principal_amount += $v['sum_repay_principal_amount'];
                    $sum_repay_trial_amount     += $v['sum_repay_trial_amount'];

                    $post_channel_repay_date               = $v['post_channel_repay_date'];
                    $post_channel_repay_asset_loan_channel = 'allen';
                    $post_channel_repay_asset_period_days  = $v['post_channel_repay_asset_period_days'];
                }
                $dataFinal[] = [
                    'post_channel_repay_date'               => $post_channel_repay_date,
                    'post_channel_repay_asset_loan_channel' => $post_channel_repay_asset_loan_channel,
                    'post_channel_repay_asset_period_days'  => $post_channel_repay_asset_period_days,
                    'sum_all_repay_amount'                  => $sum_all_repay_amount,
                    'risk_amount'                           => $risk_amount,
                    'before_should_amount'                  => $before_should_amount,
                    'after_should_amount'                   => $after_should_amount,
                    'collection_late_interest'              => $collection_late_interest,
                    'technology_amount'                     => $technology_amount,
                    'information_amount'                    => $information_amount,
                    'late_amount'                           => $late_amount,
                    'capital_service_amount'                => $capital_service_amount,
                    'capital_interest_amount'               => $capital_interest_amount,
                    'sum_repay_principal_amount'            => $sum_repay_principal_amount,
                    'sum_repay_trial_amount'                => $sum_repay_trial_amount,
                ];
            }
        }

        return $dataFinal;
    }

}