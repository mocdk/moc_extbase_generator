Command: ./typo3/cli_dispatch.phpsh extbase_generate
./typo3/cli_dispatch.phpsh extbase_generate <extension> <model1, model2, model3>

Den selv finde alle Tx_BcShop_Domain_Configuration_* klasser og bygge $classBase objekter ud fra dem

f.eks.

<?php
// ext/bc_shop/Classes/Domain/Configuration/Category.php
class Tx_BcShop_Domain_Configuration_Category extends MOC_Extbase_Generator_TCA {
   protected $table = 'tx_bcshop_categories';
}