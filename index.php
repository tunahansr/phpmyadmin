<?php
error_reporting(E_ALL);
ini_set("display_errors", true);
include __DIR__ . '/settings/include.php';
include __DIR__ . '/settings/settings.php';
@$urun = $core->VeriGetir("urunler", "slug=?", array($_GET["page"]), "ORDER BY urun_id ASC");
if ($urun != false) {
    $modulls = $core->VeriGetir("moduller", "slug=? AND durum=?", array("urun", 1));
    $config[0]["mod"] = $modulls[0];
    $config[0]["u"] = $u->detailsPage()[0];
    $gosterimUpdate=$u->GoruntulemeUpdate();
    $config[0]["benzer"] = $u->benzerUrunler($config[0]["u"]["benzer"], $config[0]["u"]["stokKod"]);
    $config[0]["sonraki"] = $u->nextProduct()["next"];
    $config[0]["onceki"] = $u->prevProduct()["prev"];
    $config[0]["urunVaryant"] = $u->urunVaryant();
    if ($u->detailsPage()[0]["yasOnayi"] == 1) {
        $alert->sweetAlert("Yaş Onayı", "18 Yaşından Büyükmüsün", "question", "Evet", '<a href="anasayfa" style="color:white;">Hayır</a>');
    }
    $pageSet = $core->VeriGetir("modulconf", "modulID=? AND durum=?", array($modulls[0]["id"], 1));
    if ($pageSet != false) {
        echo $pt->view($pageSet[0]["link"], $config[0]);
    } else {
        echo $pt->view("urun", $config[0]);
    }
}
// rooting ayarları
else {
    @$kategoriler = $core->VeriGetir("urun_kategori", "slug=?", array($_GET["page"]), "ORDER BY id ASC");
    if ($kategoriler != false) {
        $modulls = $core->VeriGetir("moduller", "slug=? AND durum=?", array("magaza", 1));
        $config[0]["mod"] = $modulls[0];
        $config[0]["filtre"] = $u->filtres();
        //$config[0]["sonuc"]=$u->filtre();
        $config[0]["pageNat"] = $u->pageNationButon($url);
        $config[0]["urunler"] = $u->categoryPage();
        $config[0]["countProduct"] = count($u->shopPage());
        $pageSet = $core->VeriGetir("modulconf", "modulID=? AND durum=?", array($modulls[0]["id"], 1));
        if ($pageSet != false) {
            echo $pt->view($pageSet[0]["link"], $config[0]);
        } else {
            echo $pt->view('shop', $config[0]);
        }
    } else {
        //? Root Alanı TODO:burda routing işlemleri yapıloyor
        if (!isset($_GET["page"]) || $_GET["page"] == "anasayfa" || $_GET["page"] == "index") {
            $config[0]["buyukBanner"] = $anasayfa->buyukBanner()["buyuk"];
            $config[0]["kucukBanner"] = $anasayfa->kucukBanner()["kucuk"];
            $config[0]["tabsCursel"] = $anasayfa->tabsCursel();
            $config[0]["buyukPList"] = $anasayfa->buyukPList();
            echo $pt->view('index', $config[0]);
        } elseif ($_GET["page"] == "magaza" || $_GET["page"] == "shop" || $_GET["page"] == "ara") {
            
            $modulls = $core->VeriGetir("moduller", "slug=? AND durum=?", array("magaza", 1));
            $config[0]["mod"] = $modulls[0];
            $config[0]["filtre"] = $u->filtres();
            //$config[0]["sonuc"]=$u->filtre();
            $config[0]["pageNat"] = $u->pageNationButon($url);
            $config[0]["urunler"] = $u->shopPage();
            $config[0]["countProduct"] = count($u->shopPage());
            $pageSet = $core->VeriGetir("modulconf", "modulID=? AND durum=?", array($modulls[0]["id"], 1));
            if ($pageSet != false) {
                echo $pt->view($pageSet[0]["link"], $config[0]);
            } else {
                echo $pt->view('shop', $config[0]);
            }
        } elseif ($_GET["page"] == "cart" || $_GET["page"] == "sepet") {
            $config[0]["sepet"] = $sepet->sepet();
            echo $pt->view('sepet', $config[0]);
        } elseif ($_GET["page"] == "favori" || $_GET["page"] == "wishlist") {
            $config[0]["favori"] = $favori->favori();
            echo $pt->view('favori', $config[0]);
        }elseif ($_GET["page"] == "hakkimizda") {
            $config[0]["hakkimizda"] = $hakkimizda->hakkimizda();
            echo $pt->view('hakkimizda', $config[0]);
        } elseif ($_GET["page"] == "iletisim") {
            $iletisimForm=$iletisim->iletisimForm();
            $config[0]["iletisim"] = $iletisim->iletisim();
            echo $pt->view('iletisim', $config[0]);
        } elseif ($_GET["page"] == "yakinda") {
            $config[0]["yakinda"] = $yakinda->yakinda();
            echo $pt->view('yakinda', $config[0]);
        } elseif ($_GET["page"] == "sss") {
            $config[0]["sss"] = $s->SSS();
            echo $pt->view('sss', $config[0]);

        } elseif ($_GET["page"] == "hesabim" || $_GET["page"] == "account") {
            $config[0]["siparis"]=$siparish->siparis();
            $adresEkleme=$a->adresEkle();
            $adresGuncelleme=$a->adresDuzenle();
            $config[0]["hesapUpdate"]=$hesapP->HesapUpdate();
            $config[0]["alankod"]=$hesapP->alankod(); 
            $hesapUpdate=$hesapP->hesapDuzenle();
            $config[0]["adres"]=$a->adres();
            $config[0]["faturalar"] = $acc->fatura();
            $modulls = $core->VeriGetir("moduller", "slug=? AND durum=?", array("hesabim", 1));
            $config[0]["mod"] = $modulls[0];
            $pageSet = $core->VeriGetir("modulconf", "modulID=? AND durum=?", array($modulls[0]["id"], 1));
            if ($pageSet != false) {
                echo $pt->view($pageSet[0]["link"], $config[0]);
            } else {
                echo $pt->view('hesap', $config[0]);
            }
        }
        elseif ($_GET["page"] == "siparis") {
            error_reporting(0);
            $payment->siparisKontrol();
            $config[0]["siparisD"]=$payment->siparisD()[0];
            $config[0]["kucukBanner"] = $siparis->siparisBanner();
            $config[0]["tabsCursel"] = $siparis->tabsCursel();
            $config[0]["aranan"]=$payment->arama();
            echo $pt->view('siparis', $config[0]);
        }

        elseif ($_GET["page"] == "odeme" || $_GET["page"] == "checkout") {
            $modulls = $core->VeriGetir("moduller", "slug=? AND durum=?", array("odeme", 1));
            $config[0]["mod"] = $modulls[0];
            echo $pt->view('odeme', $config[0]);

        }
        elseif($_GET["page"]=="dogrulama")
        {
            
            $getir=$core->VeriGetir("musteri","kayitDogrulama=?",array($_GET["kod"]),"ORDER BY musteri_id ASC");
            if($getir!=false)
            {
                $core->transaction();
                $guncelle = $core->SorguCalistir("UPDATE musteri"," SET kayitDogrulama=? , kayitDurum=? WHERE kayitDogrulama=?",array("","1",$_GET["kod"]));
                if($guncelle!=false)
                {
                    $core->MyCommit();
                    header("Location:dogrulama?basarili=Mail doğrulama işleminiz başarılı");
                }
                else
                {
                    $core->MyRollBack();
                    header("Location:dogrulama?hata=Süresi Doldu veya Hesabınız bulunamadı.");
                }
            }
            else{
                header("Location:dogrulama?hata=Süresi Doldu veya Hesabınız bulunamadı.");
            }
        }
        else {
            $config[0]["hata"] = $hata->hata();
            echo $pt->view("404", $config[0]);
        }
    }

    echo "<pre>";
    //  print_r($config);
    echo "</pre>";
  
}
