<div class="sidebar bg-primary text-white" id="sidebar">
    <h3 class="w-100 ajustetitulomobile"><?= NOME_SISTEMA ?></h3>
    <div class="text-center">
        <img src="<?= $_SESSION["avatar"] ?>" class="w-35 rounded-circle" style="object-fit: cover;"
            onclick="showEditUser()">
        <p class="mt-2">Seja Bem Vindo!
            <br>
            <em><b> <a class="text-white" href="#" onclick="showEditUser()"> <?= $_SESSION["nome"] ?> </a></b></em>
        </p>
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a id="dash" class="btn btn-primary text-left text-white nav-link active" href="#"
                onclick="showDashboardInicial()">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>
        <?php if ($_SESSION["permissao"] == "admin") { ?>
            <li class="nav-item">
                <a class="btn btn-primary text-left text-white nav-link" href="#" onclick="showUsers()">
                    <i class="fas fa-users"></i> Usuários
                </a>
            </li>
        <?php } ?>

        <li class="nav-item">
            <a class="btn btn-primary text-left text-white nav-link" href="#" onclick="showUsersCompre()">
                <i class="fas fa-dolly-flatbed"></i> <?= NOME_WOO ?>
            </a>
        </li>


        <li class="nav-item">
            <a id="shopify" class="btn btn-primary text-left text-white nav-link" href="#" onclick="showUsersShopify()">
                <i class="fas fa-award"></i> <?= NOME_SHOPIFY ?>
            </a>
        </li>



        <li class="nav-item">
            <a class="btn btn-primary text-left text-white nav-link" href="#" onclick="showUsersAcademy()">
                <i class="fas fa-user-graduate"></i>  <?= NOME_EAD ?>
            </a>
        </li>

        <li class="nav-item">
            <a class="btn btn-primary text-left text-white nav-link" href="#" onclick="showUsersLoja()">
                <i class="fas fa-bullhorn"></i> Afiliados
            </a>
        </li>

        <li class="nav-item">
            <a class="btn btn-primary text-left text-white nav-link" href="#" onclick="showUsersMarketplace()">
                <i class="fas fa-sitemap"></i> Marketplace
            </a>
        </li>

        <li class="nav-item">
            <a class="btn btn-primary text-left text-white nav-link" href="#" onclick="showUsersCorporativo()">
                <i class="fas fa-user-tie"></i> Corporativo
            </a>
        </li>


        <?php if ($_SESSION["permissao"] == "admin") { ?>

            <?php if (CENTRAL_SUPERUSER == 'superuser') { ?>
                <div class="footer-nav">
                    <li class="nav-item">
                        <a id="showCentralButton" class="btn btn-primary text-left text-white nav-link" href="#"
                            onclick="showCentral()">
                            <i class="fas fa-chalkboard-teacher"></i> Central
                        </a>
                    </li>
                </div>
            <?php } ?>            

            <div class="footer-nav">
                <li class="nav-item">
                    <a class="btn btn-primary text-left text-white nav-link" href="#" onclick="showSettings()">
                        <i class="fas fa-cog"></i> Configurações
                    </a>
                </li>
            </div>

            <div class="footer-nav">
                <li class="nav-item">
                    <a class="btn btn-primary text-left text-white nav-link" href="#" onclick="showLog()">
                        <i class="fas fa-folder-open"></i> Log
                    </a>
                </li>
            </div>

        <?php } ?>


    </ul>
</div>