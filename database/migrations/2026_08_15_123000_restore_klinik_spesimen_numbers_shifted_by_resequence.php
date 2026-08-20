<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kembalikan nomor spesimen klinik (dan kode sampel kesmas KIM/MBI)
 * yang ter-resequence oleh resequenceAutoOnlyForYear().
 *
 * Sumber: snapshot labkesmagelang_server (sebelum auto-sort).
 * Idempotent: hanya update jika nilai saat ini masih berbeda.
 */
class RestoreKlinikSpesimenNumbersShiftedByResequence extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tb_permohonan_uji_klinik_2')) {
            $this->restoreKlinikSpesimen();
        }

        if (Schema::hasTable('tb_samples')) {
            $this->restoreKesmasSampleCodes();
        }

        if (Schema::hasTable('global_lab_sequence')) {
            $this->syncSequenceCounter(2026);
        }
    }

    public function down(): void
    {
        // Tidak di-rollback otomatis.
    }

    private function restoreKlinikSpesimen(): void
    {
        $rows = [
            ['004e5366-9493-457e-ae18-8f65ead46a6c', 3589, '3589/1470'],
            ['017a3570-f6f3-4c22-beff-554a9751bf08', 3652, '3652'],
            ['056bfa9c-4eed-432a-b34e-3f84d620e54b', 3722, '3722/1581'],
            ['06206e3a-45b5-4e90-8c07-c719db7a50ee', 3756, '3756'],
            ['0654b438-7c3f-4d76-80e5-0eec34a6c66a', 3521, '3521'],
            ['07b39f67-5a4d-4cea-baf0-0c99ab2f353c', 3628, '3628'],
            ['098a0365-700e-4d3d-ae5e-40e3ffbc9eec', 3760, '3760'],
            ['0afffe57-7a53-41ab-bbec-82705386f2d8', 3700, '3700'],
            ['0ed8be47-2d24-4e3b-bba7-bca7a2366f98', 3654, '3654'],
            ['10f3a71c-2c7a-4ed1-adc8-34fee85196d8', 3759, '3759'],
            ['11c921c5-5e5c-43e8-ad9f-46a51821e502', 3528, '3528'],
            ['12074976-ee0b-49e5-96ec-ac66be71c104', 3626, '3626'],
            ['125873d8-01d9-478f-90b2-12ae5a71beb7', 3576, '3576/1459'],
            ['1293c8b4-cf87-45ea-972e-30832e3f1411', 3755, '3755'],
            ['12c2fecb-d0fb-4aab-81a3-31963cd51d3b', 3519, '3519'],
            ['135f728d-bc04-4129-814e-1456dcb6122d', 3555, '3555/1439'],
            ['14ac59e6-458d-4832-9aec-5d672576a76b', 3586, '3586/1467'],
            ['175bf24e-6aaf-44a4-9151-c8dce97d4a72', 3657, '3657'],
            ['17be183d-ef6b-487e-9796-55a5c08072cf', 3554, '3554/1438'],
            ['1a1f5e51-e945-4429-a4e0-077fe0b32da7', 3658, '3658/1520'],
            ['1e44d438-d735-495a-b7c0-194175702786', 3544, '3544'],
            ['1ed2bb89-2bab-49d5-8b1c-f030312cb172', 3643, '3643'],
            ['20361b8a-1133-42d5-a78f-0ea1e27b9d55', 3534, '3534'],
            ['2493b34f-57d5-482b-80c8-fe278ef8ec11', 3520, '3520'],
            ['280241bd-c94b-4867-8b07-a2fee735ba66', 3752, '3752'],
            ['2921e622-c387-418c-8ded-90559724c282', 3645, '3645'],
            ['2a1dbb1c-0c3f-4f9d-b9ad-7d0f83118d2c', 3637, '3637'],
            ['2af3673f-8a4e-42a4-b139-9ef0f7b2f29f', 3649, '3649'],
            ['2b3481f3-fe93-43df-91f9-bc9a30c4402d', 3678, '3678/1541'],
            ['2b88de20-135f-4e3b-8f6a-4ded63ccac1a', 3727, '3727/1584'],
            ['2d91cc7f-3476-4f33-884a-9183c166ad99', 3557, '3557/1441'],
            ['2da3f95b-23c9-486b-8791-4357d47e5e42', 3580, '3580/1463'],
            ['2e18d809-55c8-4379-b505-e5513c6750aa', 3593, '3593/1475'],
            ['300fcb46-08c3-438b-8c84-aa28176bd814', 3719, '3719'],
            ['302ecb30-7a14-4196-9b1a-6b70d785fd67', 3574, '3574/1457'],
            ['3133155d-7232-49b6-9ea8-aae3c0159d29', 3518, '3518'],
            ['322fdf5b-2718-4ed9-b471-72b568ae2f31', 3641, '3641'],
            ['33bc3f41-b862-4eb1-856c-7ea753c3a20f', 3694, '3694/1558'],
            ['341f1f48-ea5c-405a-b108-5d20c01aa291', 3540, '3540'],
            ['34be1999-1ba7-43ab-90f1-74f26ffd1d0b', 3517, '3517'],
            ['381f90d6-3106-4355-a6ef-802e9db42ce5', 3530, '3530'],
            ['39510395-96fb-4509-9882-40c5943568f2', 3600, '3600/1482'],
            ['3a678704-5aae-490c-ac2e-4d03bc18deb7', 3648, '3648'],
            ['3ae54a52-957b-477a-8a32-b1d6a5f2031b', 3792, '3792/1546'],
            ['3e260d12-ec4e-478a-a61f-787e4b87c0ee', 3710, '3710/1574'],
            ['3f4908cc-2c1d-4136-a295-2786baf6843e', 3597, '3597/1479'],
            ['43eff8d1-b0b2-4be1-9ecb-bcb22fc3ad6f', 3673, '3673/1535'],
            ['4466dfde-0f79-497a-8d40-b38404a4165c', 3537, '3537'],
            ['44e130e8-07bf-4052-b5ba-676dafa3f297', 3575, '3575/1458'],
            ['48519f4c-9c23-4401-aa3d-31f048124419', 3565, '3565/1448'],
            ['4b4543e1-4a78-4abc-908a-23f1327e8f48', 3549, '3549'],
            ['4cf4df4c-7a4a-4690-be32-61566d4147ae', 3547, '3547'],
            ['4da20775-7bc7-49a2-a8a5-1cb0725de449', 3675, '3675/1538'],
            ['4ea8c9e2-e48c-4d4b-a8ac-c4088666b412', 3703, '3703/1567'],
            ['4ed8f5c8-4728-4a2e-8863-c7a9e327391a', 3761, '3761'],
            ['5199cb02-9a47-4cf6-851c-24e0df31beaf', 3671, '3671/1533'],
            ['5224f6a6-c133-4c0b-a913-2e41d0c4d324', 3551, '3551/1435'],
            ['53654200-312c-4720-8c20-65b0eb1c201b', 3541, '3541'],
            ['5478fb9c-df8a-487e-8f12-a76fd56c75ca', 3567, '3567/1450'],
            ['55332699-4bf1-49fb-8323-5131d644cfb6', 3682, '3682/1545'],
            ['57ad8893-936c-4d8a-bede-dbf47d2f9cd3', 3592, '3592/1474'],
            ['58a51732-3dcc-4bcb-8609-157a89085857', 3751, '3751'],
            ['5a8d8299-f7a2-4ff2-8bd4-f46ac8dff24a', 3758, '3758'],
            ['5bb0b45a-7757-4cdb-814d-b9a742abeb4a', 3570, '3570/1453'],
            ['5bce3421-d937-43af-81ba-9c1dfafd1171', 3669, '3669/1531'],
            ['5d05f451-3505-45b9-b61b-58d56e3a0a77', 3639, '3639'],
            ['5d63fcbb-a754-4218-bba2-0e0e673cf9b6', 3553, '3553/1437'],
            ['5d6f284b-5f69-4c62-a274-979748323b1a', 3686, '3686/1550'],
            ['5df96caa-efdd-4689-940f-b9e2623fba7e', 3750, '3750'],
            ['5e0f598a-e7c8-4a0e-9d92-dea693fef9fd', 3532, '3532'],
            ['5ed132af-19c6-4728-97d6-6a7e36569a2d', 3627, '3627'],
            ['5fc26c57-e96e-4cdb-9fe9-2920aa91211c', 3556, '3556/1440'],
            ['5fcfd1ed-ac2a-4313-b981-896736cadec7', 3542, '3542'],
            ['5fdd0712-15b6-4b7f-917b-fcc623a8e3fa', 3594, '3594/1476'],
            ['61708004-f24e-42c0-928d-804d564546e8', 3562, '3562/1445'],
            ['66be9cc2-b9a3-40da-a24a-d2dabbe50f35', 3525, '3525'],
            ['675e9196-28aa-4fb7-8a64-1f96693f63d8', 3693, '3693/1557'],
            ['68d2234c-0fb7-45ac-ab96-aa0b0b453b7e', 3571, '3571/1454'],
            ['6d0a6b44-2025-420c-800d-de8d164ea285', 3715, '3715/1577'],
            ['6d7505dd-ae9f-4973-9956-0e00c8e628cf', 3718, '3718'],
            ['6e2e8111-9617-444c-99a2-7ee159fe740e', 3632, '3632'],
            ['73aed6d4-ee1b-4aec-b535-6e71d1efeb88', 3723, '3723/1582'],
            ['74ceee0e-b073-46fd-bb5b-057d04c33b79', 3790, '3790'],
            ['77f9192e-d77b-4309-8d88-d6dd73382627', 3535, '3535'],
            ['78d349f4-517c-4209-a977-1d003adc3c3a', 3762, '3762'],
            ['7b380d47-558d-4868-ba49-733ef07e48f0', 3681, '3681/1544'],
            ['7b9ec011-164f-40a5-be34-171a69e9fb97', 3663, '3663/1525'],
            ['7bc58485-e113-457b-ab61-6d856a82fe4d', 3529, '3529'],
            ['7bccc095-09ae-447e-b512-063539e14915', 3666, '3666/1528'],
            ['7ce18a84-8cfa-4a97-be0f-125189b0a5c8', 3661, '3661/1523'],
            ['7f2325c2-67e0-49fc-b5ab-0fda64e0772c', 3484, '3484'],
            ['7ff28d50-ea63-41ec-bd79-2f59d8b943b3', 3763, '3763'],
            ['80d39e90-74c5-493e-b242-5e27b34ac6c6', 3653, '3653'],
            ['80f17fcb-fe62-4b25-a9ef-2fb1eaba8811', 3552, '3552/1436'],
            ['8355ca43-cea7-490e-8c29-4282b3ca1d37', 3753, '3753'],
            ['8365c43b-02d4-417c-ae53-c9f1beea2fdb', 3539, '3539'],
            ['87a12aff-5335-4200-b86b-c91d373d9720', 3724, '3724/1583'],
            ['8883b658-addc-4300-ada0-089fa6f4cab0', 3660, '3660/1522'],
            ['8979bffd-4bc8-4ecb-8d2b-6e63483f2ce8', 3701, '3701/1565'],
            ['8a0d1d86-9355-4dc2-ad82-87337dd6b31c', 3646, '3646'],
            ['8a32bdcd-f1fe-464e-81db-426f9d5a3194', 3598, '3598/1480'],
            ['8a3307b7-4d09-439c-b9f7-427d2ddcab21', 3674, '3674/1537'],
            ['8a8ea07c-92b0-4a88-b10f-cba34c79f4db', 3527, '3527'],
            ['8c920930-dab9-466b-b7ee-0803970d7914', 3709, '3709/1573'],
            ['8defc36b-c1e6-4362-914b-fae57e55bba0', 3716, '3716/1578'],
            ['903ad7ec-3e37-485d-a753-78e2d6691f9c', 3558, '3558/1442'],
            ['91e5ffae-291e-43a7-9db4-c276ecd2d9a3', 3699, '3699/1563'],
            ['92f763ff-d7ae-40f3-a007-ead78a72860a', 3683, '3683/1547'],
            ['93245e45-296c-46ca-bb59-9ae79dda3170', 3577, '3577/1460'],
            ['9595705c-3d7e-429b-a158-d22b4744c35e', 3708, '3708/1572'],
            ['96abf8a1-fbe5-42d9-9510-74bd2157e2ec', 3664, '3664/1526'],
            ['97587d33-fc38-4a7c-aecf-aa6b1fdf2182', 3536, '3536'],
            ['99cc5555-1515-4c41-b06a-0a4f92906dab', 3662, '3662/1524'],
            ['9b05bb1e-1f57-4e06-88ca-af885c30eeaa', 3691, '3691/1555'],
            ['9f840e41-c15f-4692-883a-2b58aaf61fb2', 3690, '3690/1554'],
            ['a1399ae2-a121-4358-8d79-5136af597f36', 3711, '3711/1575'],
            ['a1609fe0-590d-42a0-a80e-3218c21f45ae', 3696, '3696/1560'],
            ['a45b0bce-5be2-4017-8f82-58389432e0b5', 3668, '3668/1530'],
            ['a5561478-d5c9-4885-8df2-286cf0552467', 3687, '3687/1551'],
            ['a5f1ffb5-5c3d-4448-8346-bd954fa50995', 3587, '3587/1468'],
            ['a87e6eea-68b4-46fe-9891-055c1b7af1ef', 3595, '3595/1477'],
            ['a8a9bb3a-1f6f-4bdf-8f57-5059c0f35ffc', 3638, '3638'],
            ['abf174ca-551e-4da4-bd3f-9b2508ceae38', 3667, '3667/1529'],
            ['abf2d5a5-8680-41fd-9149-1b85c1557e21', 3720, '3720/1580'],
            ['ad00c809-af71-4700-b292-e9b3b88bd6b8', 3698, '3698/1562'],
            ['ae16be03-77c0-42d1-b8a4-7c7d5fd23ae5', 3636, '3636'],
            ['b1cede2a-a98a-4f7f-9a5d-574825a030fb', 3656, '3656'],
            ['b2200c1d-2021-4ff9-bb46-096adf6e4283', 3548, '3548'],
            ['b42a8883-4207-4cc4-a424-6b3ea590470c', 3543, '3543'],
            ['b47eff8f-d1c2-482a-b647-174a54427018', 3545, '3545'],
            ['b74f95a1-9282-4035-ada2-1fbd5535d54a', 3524, '3524'],
            ['b7ed9216-1d77-4080-8347-fdebd143979d', 3655, '3655'],
            ['b86d95e7-5824-4e1c-ae2d-d93dd7243448', 3522, '3522'],
            ['b87fcf53-b685-42f0-8803-459bb9c91815', 3647, '3647'],
            ['b94a8710-8eb5-47fe-a605-762ad39c1248', 3633, '3633'],
            ['b9f993f5-4a4d-4c32-a7b3-9f345265aa2b', 3791, '3791/1536'],
            ['ba043eda-cbdc-4c80-b077-69c4cee34744', 3538, '3538'],
            ['ba44eaf2-25b0-4a1e-8eb0-88624146eeea', 3714, '3714/1576'],
            ['bca29fc4-b3e2-4d80-9568-29219e4fefa2', 3644, '3644'],
            ['bd9667c5-3959-4f7b-a1ac-50f4b3363eb4', 3684, '3684/1548'],
            ['c1216a8e-0fa5-45fc-ab77-32ca92e57789', 3584, '3584/1465'],
            ['c17142f4-56e1-434c-8753-46823e8954c8', 3688, '3688/1552'],
            ['c215b392-d11a-4f3b-8e7c-c576beb727d3', 3717, '3717/1579'],
            ['c2bfe5ab-5d86-45aa-bdbe-c1fb3c787f1b', 3677, '3677/1540'],
            ['c326809c-875c-4030-8923-65b7ed0bd5d4', 3764, '3764'],
            ['c42d6f04-0803-4be9-97ab-a81c9fff7b5e', 3579, '3579/1462'],
            ['c5b2fd15-57e1-4659-b3f9-374cf20e6c16', 3488, '3488'],
            ['c631a780-5911-4dbb-b1ac-0ea0795f30f7', 3680, '3680/1543'],
            ['c64cb2be-7871-4277-a73f-922334c88de4', 3697, '3697/1561'],
            ['cb6946ae-d7bf-40bc-a91a-f10cf91130ee', 3533, '3533'],
            ['cc47d94d-0ffa-4e42-b2cd-efb5ba42cb3c', 3635, '3635'],
            ['cc9c4395-c294-4527-8349-5415e2b972da', 3604, '3604'],
            ['ce7ec716-505d-457d-b8a5-dd6769d1bc8a', 3630, '3630'],
            ['cf3df712-6d9b-4521-94b1-ce95e43ea3bb', 3602, '3602'],
            ['d0337eca-8d93-4616-a611-a63f0d7a2d42', 3749, '3749'],
            ['d1530e89-c5ba-4dc1-803c-fb194e503fa2', 3568, '3568'],
            ['d2097c5d-3303-4e9d-bd20-ef5780881bd8', 3665, '3665/1527'],
            ['d313aa19-7454-424c-8ec5-28de3c99c815', 3629, '3629'],
            ['d3cd6d7d-427b-4b76-8c46-3fd4df4a45c8', 3692, '3692/1556'],
            ['d5449eaa-a0c2-474b-aa38-0c0eb3735d7f', 3563, '3563/1446'],
            ['d7f8d1a3-111d-4755-a789-88fc788b66f5', 3754, '3754'],
            ['dc43d81f-ef7a-4864-999d-06eb28caf15e', 3702, '3702/1566'],
            ['dd42cb0f-f26e-440f-86f8-16f8f5845fad', 3707, '3707/1571'],
            ['de3aa5e4-182c-4a39-b539-bc445a30df5e', 3569, '3569/1452'],
            ['de475da2-3a7b-4a87-b3da-277b7c0a1ecc', 3640, '3640'],
            ['de81db2d-c223-46b1-84eb-0d34df1ab33d', 3631, '3631'],
            ['dee6b86e-937c-413e-8bfb-58544569b07b', 3706, '3706/1570'],
            ['dfc68f17-1e76-429e-8840-29bce5db30bf', 3670, '3670/1532'],
            ['e0358213-b765-4c5d-a1c6-aa64cdc4374d', 3561, '3561/1444'],
            ['e3081118-282d-4c99-b559-49afb5fa0e48', 3605, '3605/1472'],
            ['e36fce8d-ccd6-467e-9326-60657e600505', 3642, '3642'],
            ['e3ac155a-4b94-439b-b21d-79dabf13bbb5', 3757, '3757'],
            ['e6591753-753a-4cab-8487-46ebae66a273', 3659, '3659/1521'],
            ['e69837f9-a2d7-47f0-a85f-8376d903660b', 3564, '3564/1447'],
            ['e73e8fd9-8ad8-4045-b659-fd561a59a9f0', 3695, '3695'],
            ['e74bba41-0c1f-4d6c-91fd-3e7edb02ed0c', 3550, '3550/1434'],
            ['e960889a-a336-49d7-95d8-980d5c6aceba', 3526, '3526'],
            ['eb5a9034-7b13-4629-9de7-68d975855ea6', 3650, '3650/1512'],
            ['eb8faea4-0245-4590-8007-8eb5f5e432f2', 3596, '3596/1478'],
            ['ee54aebb-ebc3-4975-9927-fbb788f4db11', 3704, '3704/1568'],
            ['ee9d4c21-43d7-46a8-803f-ffdf825c04ef', 3651, '3651'],
            ['f2dbcac7-0226-4849-9e2c-53a6c4f6efa0', 3566, '3566/1449'],
            ['f414263d-f363-4893-a2d0-0702fea2c79b', 3599, '3599/1481'],
            ['f4194274-4203-4c49-a422-2b990f48183e', 3573, '3573/1456'],
            ['f4477ecc-5dd8-4e85-9c05-8e06ba942a38', 3679, '3679/1542'],
            ['f5a4ce57-f08b-40ac-beae-a84389442372', 3531, '3531'],
            ['f9a33f86-a364-4337-8b02-8bf44a4e1bcf', 3672, '3672/1534'],
            ['f9c70941-b05e-46ac-8fdc-e7845a50cd61', 3572, '3572/1455'],
            ['fa4c4b88-ff86-4cdb-9052-471f07ce24e3', 3676, '3676/1539'],
            ['fc816a6f-3e14-4981-afb2-9260f59c0c81', 3789, '3789'],
            ['ffb3d70b-44a4-48f4-8542-b8444bddc67b', 3560, '3560/1443'],
            ['fff45f44-7508-461c-8c21-b944e30aa692', 3685, '3685/1549']
        ];

        foreach ($rows as $row) {
            [$id, $nourut, $noreg] = $row;

            DB::table('tb_permohonan_uji_klinik_2')
                ->where('id_permohonan_uji_klinik', $id)
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->where('is_nomor_spesimen_manual', 0)
                        ->orWhereNull('is_nomor_spesimen_manual');
                })
                ->where(function ($q) use ($nourut, $noreg) {
                    $q->where('nourut_permohonan_uji_klinik', '<>', $nourut)
                        ->orWhere('noregister_permohonan_uji_klinik', '<>', $noreg);
                })
                ->update([
                    'nourut_permohonan_uji_klinik' => $nourut,
                    'noregister_permohonan_uji_klinik' => $noreg,
                    'updated_at' => now(),
                ]);

            $this->claimSequenceNumber('klinik', $id, (int) $nourut, 2026);

            if (Schema::hasTable('tb_number_klinik')) {
                $padded = str_pad((string) $nourut, 4, '0', STR_PAD_LEFT);
                DB::table('tb_number_klinik')
                    ->where('id_permohonan_uji_klinik', $id)
                    ->whereNull('deleted_at')
                    ->update([
                        'new_number' => $padded,
                        'last_number' => $padded,
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    private function restoreKesmasSampleCodes(): void
    {
        $rows = [
            ['021e1a40-dcfa-435e-bf5e-fa8c1c1a9196', 'AM.02/1051/2026', 1051, 'df412e8e-c1cc-4df8-9517-4f82605051a6', 1051],
            ['03c15de8-b21e-457b-b35d-25bc3ff3ecc6', 'UA.02/3607/2026', 3607, '26a292a6-3f86-4ec7-9837-3f49e464216a', 3607],
            ['04fb73a9-2361-4e0d-a198-eef243d9d935', 'AM.01/3735/2026', 3735, 'e5aefeb1-78e1-4dc0-b321-e3156b8f7898', 3735],
            ['073e6d7f-0e4c-4def-bb70-7a88d8a18acf', 'MM.02/3732/2026', 3732, 'bd8c96e8-3d89-4a2f-9d84-4abc0da60499', 3732],
            ['08adad7a-8f0d-40d1-9710-f6326dc7ef14', 'MM.01/0555/2026', 555, 'ddb63f5d-303a-436e-8a74-a9e31f65ea54', 555],
            ['0f15f5ac-f112-44d4-9d64-37facd695df2', 'AM.02/3620/2026', 3620, 'f75f7f5f-7aa4-4301-a124-c4b8d616e02b', 3620],
            ['118efa9c-ab5f-4a83-9bc3-6c5e1d2f0e51', 'MM.01/3729/2026', 3729, 'ca051802-394c-42de-a10b-cea5053fc720', 3729],
            ['14cfd335-e23a-4ec7-a1f5-2ac214df68c3', 'MM.01/3731/2026', 3731, 'de299369-5237-4855-8380-fc863a6eea41', 3731],
            ['174a30b7-2bbf-447b-93aa-d71a18281b3e', 'AM.02/3746/2026', 3746, '8618d982-767a-4713-bbf2-2638f8f5773f', 3746],
            ['1e2c711e-1fd7-4951-8cc1-034952ee3899', 'AM.01/3747/2026', 3747, '828df4b9-1824-4d43-a933-33b3ba1d3f93', 3747],
            ['266712d7-f7f7-474f-b752-3d5df12bdc79', 'UA.02/3608/2026', 3608, '27ffdb78-931c-4049-a4b5-2f53ea76fe64', 3608],
            ['26f7a4d9-289a-466a-bb14-f2e19168538b', 'AM.02/3510/2026', 3510, '74374754-e056-4551-bb79-8da910bc4104', 3510],
            ['275295a2-1b6c-45b8-b927-317798a0a1e7', 'AM.02/3748/2026', 3748, '99a70cea-aa6a-49ad-a7b8-909acdee2321', 3748],
            ['2933c406-5d8f-422a-8614-0eb7a4bf2646', 'AM.02/1052/2026', 1052, 'a1f2c3ab-1174-467d-8cb9-524125828743', 1052],
            ['29a62622-db8e-4012-9772-603950cc8cf3', 'AM.02/1053/2026', 1053, '9f4a04be-5c89-431b-83fc-d964e15b6c66', 1053],
            ['29cc9dde-0a53-4f1b-94b8-1d0bd177ee8d', 'AM.01/3489/2026', 3489, 'caf220d8-23f8-42b6-93a6-f2e0885e8463', 3489],
            ['2dcdea62-3016-4dee-81f9-d105ebbfef5d', 'MM.02/0367/2026', 367, 'c80e3e60-8146-4b34-b4c0-f73869f2eccc', 367],
            ['2f84a23d-ab17-46b3-b39a-6a27fb50dceb', 'MM.01/0960/2026', 960, 'b0256a40-3444-47f2-8655-406ce0d96cb4', 960],
            ['31f6ee40-a935-4f9b-9e39-5fb9821241f1', 'MM.01/0366/2026', 366, 'd2cb3f67-4bc9-48fc-a5de-13f0dd67edef', 366],
            ['35e3d3b4-9b77-4480-b6c0-86927f7859fb', 'AM.02/3508/2026', 3508, '70305852-a373-4409-a8da-0f809a42e7b2', 3508],
            ['370f7b43-3dc6-48cb-a540-c7dc86cea140', 'UA.02/3609/2026', 3609, '46646bd8-c28d-4661-a6ff-da278a0200b6', 3609],
            ['3944058b-c5dd-4610-90e7-650dd6364866', 'AM.01/3511/2026', 3511, '9d001aad-8461-4b12-b3ba-6e5c91bdc4dd', 3511],
            ['3976c3dc-9637-4a76-989d-91972500c3b0', 'AM.01/3582/2026', 3582, 'c746f5d8-fde5-4c69-bbe8-af70032004cd', 3582],
            ['3e4d8ee3-b79f-424b-8e3b-e4e92b1b28ef', 'AM.02/3490/2026', 3490, 'caf74cb6-d7dc-48ac-93f0-98f86a38a662', 3490],
            ['429940d9-756e-4a8c-8059-0ba148def325', 'AM.02/3494/2026', 3494, '5b7f6486-bfea-485b-9bec-d71e1fa0ab61', 3494],
            ['45e371b9-0535-4f7d-a869-85f82ea59267', 'AM.01/3615/2026', 3615, '3b42ee50-c71e-4cbf-b06b-81bab6c87029', 3615],
            ['48ecc792-bb99-411e-baf8-3481ab3f3503', 'UA.02/3610/2026', 3610, '5c1cf790-bf99-4b71-8343-4a0756b41367', 3610],
            ['4d3e9341-954c-49b1-bbe3-7f66b69fb68e', 'AM.01/3741/2026', 3741, '5b445f77-2f7d-46d7-b9bb-3acd3af50560', 3741],
            ['5ac31206-e283-4b56-84fd-44e87e6769ec', 'AM.01/3509/2026', 3509, 'c7803d02-ebca-4d48-8074-55e4c8ced10f', 3509],
            ['5bcda51c-d0c3-42e6-8818-36c9b499f6a5', 'AM.01/3513/2026', 3513, 'ca59c665-468f-4c0a-8466-7435aaf30018', 3513],
            ['5eb56e6e-b404-4354-971a-2ebee914d829', 'MM.01/3728/2026', 3728, '90e920a1-9578-469f-acbe-1e4d950c2b51', 3728],
            ['655d9b69-7e39-4508-a93e-d061663e2c1a', 'AM.02/3500/2026', 3500, '48327f18-e760-4a0d-a04f-b455a458c2bb', 3500],
            ['6565435d-d29d-4d2e-9c31-d6024ed5eae6', 'AM.02/3614/2026', 3614, '6c4ede88-f7a2-42ea-ab5f-7548b46097e2', 3614],
            ['657613a4-db1d-4101-b217-cc8dfb2b6eca', 'AM.01/3737/2026', 3737, '33202856-c35c-445d-ac3d-e89d579903bc', 3737],
            ['690d0342-5034-46e4-9b27-908b34567f60', 'AM.01/3743/2026', 3743, 'f9b69c01-1d29-418b-bb06-9b332aabacc3', 3743],
            ['6b893290-932e-486c-8631-2c09ee5892d5', 'AM.01/3505/2026', 3505, 'a094c356-7a00-4556-ab3d-acb963bec64e', 3505],
            ['72843468-1ef7-4d57-8315-765f6a179011', 'AM.02/3514/2026', 3514, 'ed3db24e-b39c-4238-9eea-1c536f4d750a', 3514],
            ['7331b55e-9d6b-4569-9057-dc9f06949746', 'AM.02/3512/2026', 3512, '82496c24-f2b4-4e15-86db-b08ae32877b0', 3512],
            ['73480973-ae1b-42a5-9ec2-8d37c028096e', 'AM.01/3503/2026', 3503, '7687766d-3bc5-4112-a0cb-aa0697bfb4f3', 3503],
            ['741a82a8-4c46-4131-9599-0bded66b5820', 'AM.01/3621/2026', 3621, '7edccf90-99d5-40d1-a082-bcf8b73fb9e3', 3621],
            ['754411f5-d717-4559-b9cf-f7a6c6fefab4', 'AM.02/3496/2026', 3496, 'e74df713-9417-4219-b574-c8983bf78e70', 3496],
            ['787c0db1-b3c8-4e79-ab3f-ce3bdf28e0ce', 'AM.02/3622/2026', 3622, 'e450f3a1-ca05-46c6-8ffe-29310fa763cd', 3622],
            ['7ea02262-6b88-462b-928b-088f32b57a05', 'MM.01/3721/2026', 3721, 'aa982355-4f42-47d2-9726-608830804b98', 3721],
            ['7fbb5dee-c557-4ef6-841d-8539520de9da', 'AH.02/3612/2026', 3612, 'f428d37d-f386-4bcf-aecc-77c2c71c693c', 3612],
            ['8576263d-8033-442f-b7be-ff96221ffbd7', 'AM.01/1049/2026', 1049, '0e8b930d-79f7-412a-b93a-5f3ee9cd96dd', 1049],
            ['86fbf3f5-848f-48be-aedc-fbbd0708b298', 'AM.01/3619/2026', 3619, '1d6b8a85-713b-4786-9527-7bad519568b0', 3619],
            ['870c10a0-b929-4dcb-bfdd-2c45bf483873', 'AM.02/3742/2026', 3742, 'd08cceaa-971b-4be9-8ab3-532685931947', 3742],
            ['89fc703f-946a-497a-804c-270b1f277126', 'MM.01/3559/2026', 3559, '43e02749-cbcb-49d7-bdcc-ad9cc7b36673', 3559],
            ['8d866418-3e09-44bf-ab87-3bb7af4c70c7', 'AM.02/3616/2026', 3616, '83550b47-7af9-4461-95e7-8e011e30d0c9', 3616],
            ['8dff45e4-77af-493d-960b-438f1945c954', 'AM.01/3495/2026', 3495, '1e731aff-8508-4d84-861f-52a808ed405d', 3495],
            ['8f08fd05-87ab-44aa-91b5-7271bcd02b5a', 'AM.01/3507/2026', 3507, '2e4b4f90-0c42-4a40-bf06-d16343f977aa', 3507],
            ['96a54113-37f1-418c-b467-43982922cb14', 'AM.01/3617/2026', 3617, 'd79e4d5a-a3e4-428a-9379-ada1c43f1d50', 3617],
            ['98820983-bb25-4158-9b6a-7b2855340fda', 'AM.01/3499/2026', 3499, '3c65038c-6488-44f3-b726-e1fcf6063a88', 3499],
            ['9a51d09e-0a23-481f-9657-97ea61beb2ef', 'MM.02/0365/2026', 365, 'f44ab4c1-1690-4f58-8e56-104458920cdd', 365],
            ['9ca4a89d-a6ce-4fc1-9f9e-7d387e36f095', 'MM.01/3487/2026', 3487, '6e0d087c-7150-4475-880e-01d782927e84', 3487],
            ['9e738e12-34f8-4872-87d0-0d608c6a5ff6', 'AM.02/3504/2026', 3504, '4753c9a5-c347-4427-b053-69b9fcf16cab', 3504],
            ['9f0db332-2d9b-449c-b63d-7d08713c71e0', 'AM.01/3623/2026', 3623, '16969263-765e-463b-a9bc-3236fee3d5f3', 3623],
            ['a095492b-ad9a-4300-ad30-8ad9adecf7fd', 'UA.02/3606/2026', 3606, '3bd9e0e9-d0c2-45eb-958e-2f08e609b9f3', 3606],
            ['a2ddd0c0-1293-4af5-96a8-6011f59162e7', 'AM.02/3736/2026', 3736, 'af8ee4d3-4d0d-4044-b0ca-949175c75afd', 3736],
            ['a92c11bf-f7d8-4a6b-891f-22cec29531c0', 'MM.01/3726/2026', 3726, '3d47a9b6-d448-4467-a59b-801c46817530', 3726],
            ['ab75e8c8-59a3-4950-bb38-fd7625a8e4e6', 'AH.01/3712/2026', 3712, 'b294e125-2f4e-4282-801b-73b001af9d6d', 3712],
            ['b8b77f15-2b25-43c7-879e-630af84a2fed', 'AM.01/1013/2026', 1013, '6bbd9145-4f29-40df-b85c-c83556d07903', 1013],
            ['b9ec933b-c88d-481e-8b99-fd765099986b', 'AM.02/3583/2026', 3583, '5a0f1644-57da-4160-98d5-75d4b1f13097', 3583],
            ['bd76f780-383f-45cf-864e-e0420a5732ca', 'AH.02/3713/2026', 3713, '919425c1-a50c-4b3e-a2a6-b3cf7bff4ec2', 3713],
            ['bd858643-69d8-4f70-99b1-8a156129b742', 'AM.02/3624/2026', 3624, '6e65107a-fa8b-4f12-83b1-1845eee22fa2', 3624],
            ['be7f6ec2-3e9f-4b0f-95e9-c3e942738d12', 'AM.01/3493/2026', 3493, '2f8a8bff-faf4-4f34-af93-92d7f13cad91', 3493],
            ['bf359457-3b83-4da8-9bee-3e31b3a2ad3c', 'UA.02/3611/2026', 3611, '597a189a-a118-40ef-bff5-8469cca3cf47', 3611],
            ['c147c1d8-5118-429a-8ae3-db52fb429c8b', 'AM.01/3491/2026', 3491, '3efb411e-510d-4f33-b4bf-b9cf68056779', 3491],
            ['c5b52384-8497-4d18-a3c6-eb4d14eed72e', 'MM.02/3733/2026', 3733, 'a95396b4-e186-42bc-a423-1ef8d5c45fb4', 3733],
            ['c90423d8-c632-44c7-9636-590ede302521', 'MM.01/0992/2026', 992, '2e0f7e49-07f5-47c5-aaa9-59b24096556a', 992],
            ['cc3e44d4-84b4-407a-8731-1d2efe22362c', 'AH.01/3613/2026', 3613, '800e52e1-29c9-434d-a3ca-93d469e99eb1', 3613],
            ['cebab8a1-3b3c-4ea0-afaf-c72768d49cb5', 'MM.01/3730/2026', 3730, '57aa3efa-e50f-469e-b013-4e9335176317', 3730],
            ['d7e64900-7eae-4086-b47b-2cc445882085', 'AM.01/3739/2026', 3739, '966df9d1-58f4-438b-ba6d-fe0c077ca37a', 3739],
            ['d8a6d23b-5673-4c5e-9048-3e4259c50d97', 'AM.02/3738/2026', 3738, '4c7d37da-e413-4e4e-8f04-2c5461cfebf0', 3738],
            ['e3bc18ef-88c5-4169-904e-989cc9d72a70', 'MM.02/3486/2026', 3486, '43bd1872-be24-4325-b127-1cd782a99856', 3486],
            ['e5651420-add9-4efd-af6f-75ca196835ba', 'AM.01/1050/2026', 1050, 'fa479134-0aed-4ccc-9e25-568382a7117b', 1050],
            ['e7f325e4-b81f-4a3c-8bb7-b61959bf738d', 'AM.02/3506/2026', 3506, 'cde1ec6b-2f63-4bf4-b17d-8c36553b064b', 3506],
            ['efebd936-75ba-47d5-8d96-6b2bd81e9e31', 'AM.02/3625/2026', 3625, '3b9f609b-d3f4-417e-a615-1eca5ac250e9', 3625],
            ['f45b1d99-0fd3-4721-a861-132e57698666', 'AM.02/3618/2026', 3618, '1d435563-5e75-489b-9537-d2fb67f5da06', 3618],
            ['f45f5db6-421c-4314-b9e3-ab1707f314c3', 'AM.02/3740/2026', 3740, 'dd080705-e1e3-478a-b1ff-f72eb8befaf8', 3740],
            ['f47b86f8-a028-42bf-813f-a18aa5615243', 'MM.01/3725/2026', 3725, 'fc038d1b-7bf9-4977-aaab-aaac7af1788d', 3725],
            ['f661ed17-0e6f-4808-bfb3-c8ddcb1dfac6', 'AM.02/3492/2026', 3492, '7df60d5e-b552-4109-9f1c-3eac340f6844', 3492],
            ['f713434f-ff6f-470a-b06f-a35b1abd92a2', 'AM.02/3744/2026', 3744, '85f16d5d-922c-461f-9e6f-9dc4222cf50f', 3744],
            ['fcde3c1b-a4e7-4a4e-a34d-e06fd97c17b2', 'AM.01/3745/2026', 3745, '00fb6021-2673-4b40-82d9-c32d8fae0edd', 3745]
        ];

        foreach ($rows as $row) {
            [$id, $code, $countId, $labNumId, $labNumber] = $row;

            DB::table('tb_samples')
                ->where('id_samples', $id)
                ->whereNull('deleted_at')
                ->where(function ($q) {
                    $q->where('is_nomor_sampel_manual', 0)
                        ->orWhereNull('is_nomor_sampel_manual');
                })
                ->where(function ($q) use ($code, $countId) {
                    $q->where('codesample_samples', '<>', $code)
                        ->orWhere('count_id', '<>', $countId);
                })
                ->update([
                    'codesample_samples' => $code,
                    'count_id' => $countId,
                    'updated_at' => now(),
                ]);

            if ($labNumId && $labNumber !== null && Schema::hasTable('tb_lab_num')) {
                DB::table('tb_lab_num')
                    ->where('id_lab_num', $labNumId)
                    ->whereNull('deleted_at')
                    ->where('lab_number', '<>', $labNumber)
                    ->update([
                        'lab_number' => $labNumber,
                        'updated_at' => now(),
                    ]);
            }

            $seqRef = $labNumId ?: $id;
            $seqNum = (int) ($labNumber ?: $countId);
            if ($seqNum > 0) {
                $this->claimSequenceNumber('lab', (string) $seqRef, $seqNum, 2026);
            }
        }
    }

    /**
     * Set one active sequence_detail to {year, number, type, reference}.
     * Soft-delete duplikat/orphan yang memegang nomor yang sama (unique active_sequence).
     */
    private function claimSequenceNumber(string $labType, string $referenceId, int $number, int $year): void
    {
        if ($number < 1 || !Schema::hasTable('global_lab_sequence_detail')) {
            return;
        }

        $now = now();
        $ownIds = DB::table('global_lab_sequence_detail')
            ->where('lab_type', $labType)
            ->where('reference_id', $referenceId)
            ->whereNull('deleted_at')
            ->orderBy('created_at')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $keeperId = $ownIds[0] ?? null;
        $extraOwn = array_slice($ownIds, 1);
        if (!empty($extraOwn)) {
            DB::table('global_lab_sequence_detail')
                ->whereIn('id', $extraOwn)
                ->update(['deleted_at' => $now, 'updated_at' => $now]);
        }

        $occupants = DB::table('global_lab_sequence_detail')
            ->where('year', $year)
            ->where('sequence_number', $number)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->all();

        foreach ($occupants as $occupantId) {
            if ($keeperId && $occupantId === $keeperId) {
                continue;
            }
            DB::table('global_lab_sequence_detail')
                ->where('id', $occupantId)
                ->update(['deleted_at' => $now, 'updated_at' => $now]);
        }

        if ($keeperId) {
            DB::table('global_lab_sequence_detail')
                ->where('id', $keeperId)
                ->update([
                    'year' => $year,
                    'lab_type' => $labType,
                    'reference_id' => $referenceId,
                    'sequence_number' => $number,
                    'updated_at' => $now,
                ]);

            return;
        }

        $freed = DB::table('global_lab_sequence_detail')
            ->where('year', $year)
            ->where('sequence_number', $number)
            ->whereNotNull('deleted_at')
            ->orderByDesc('updated_at')
            ->value('id');

        if ($freed) {
            DB::table('global_lab_sequence_detail')
                ->where('id', $freed)
                ->update([
                    'lab_type' => $labType,
                    'reference_id' => $referenceId,
                    'sequence_number' => $number,
                    'year' => $year,
                    'deleted_at' => null,
                    'updated_at' => $now,
                ]);
        }
    }

    private function syncSequenceCounter(int $year): void
    {
        $maxKlinik = (int) DB::table('tb_permohonan_uji_klinik_2')
            ->whereNull('deleted_at')
            ->whereYear('created_at', $year)
            ->max(DB::raw('CAST(nourut_permohonan_uji_klinik AS UNSIGNED)'));

        $maxSample = 0;
        if (Schema::hasTable('tb_samples')) {
            $maxSample = (int) DB::table('tb_samples')
                ->whereNull('deleted_at')
                ->whereYear('created_at', $year)
                ->max('count_id');
        }

        $target = max($maxKlinik, $maxSample);
        if ($target < 1) {
            return;
        }

        DB::table('global_lab_sequence')
            ->where('year', $year)
            ->whereNull('deleted_at')
            ->where('last_number', '<', $target)
            ->update([
                'last_number' => $target,
                'updated_at' => now(),
            ]);
    }
}
