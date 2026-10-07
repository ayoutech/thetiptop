// Jenkinsfile — Thé Tip Top
// Pipeline : develop (auto) -> preprod (auto) -> master (validation humaine + blue/green sur Render)
// Les secrets (hooks Render) sont TOUJOURS lus via Jenkins Credentials, jamais codés en dur.
//
// Traçabilité blue/green :
//   - active_color.txt   : couleur actuellement servie en prod (blue|green), versionnée dans le repo.
//     'blue' = service Render "thetiptop-blue", 'green' = service Render "thetiptop-green".
//     Le service public "thetiptop" (https://thetiptop.onrender.com) est un routeur (dossier router/)
//     qui lit cette valeur sur GitHub et redirige le trafic vers la couleur active.
//   - last_stable_tag.txt: dernier commit déployé avec succès et validé en prod (traçabilité DORA).
//
// Tests (AVANT toute construction d'image) : un type de test = un stage, dans cet ordre :
//   1 lint PHP -> 2 unitaires -> 3 intégration (MariaDB) -> 4 API (HMAC) -> 5 sécurité (SAST + Trivy)
//   -> 6 end-to-end (HTTP puis navigateur). Un échec arrête la chaîne : pas d'image, pas de déploiement.
//   Après déploiement : tests de fumée (version servie + en-têtes) et test de performance k6 sur DEV.
//   Scripts : ci/*.sh ; suites : tests/ ; rapports JUnit publiés dans Jenkins (onglet « Tests »).
//
// NB: l'agent Jenkins n'a pas PHP installé -> toutes les commandes php (lint, tests) tournent
// à l'intérieur de l'image Docker du projet via `docker run`, jamais directement sur l'agent.
//
// NB anti-boucle : chaque déploiement/rollback fait un commit+push automatique
// (active_color.txt / last_stable_tag.txt) vers master, ce qui redéclenche le webhook
// GitHub -> Jenkins. Sans protection, ce nouveau build attendrait une approbation
// manuelle et referait un déploiement inutile (ou une boucle infinie). Le stage
// Checkout détecte si le dernier commit vient de "Jenkins CI" lui-même et, si oui,
// fixe env.SKIP_CI=true : tous les stages suivants sont alors sautés proprement.

// Publie un rapport JUnit sans faire échouer le build si le plugin JUnit est absent.
def publishReport(String pattern) {
    try {
        junit allowEmptyResults: true, testResults: pattern
    } catch (Throwable e) {
        echo "Rapport JUnit non publié (${e.message})"
    }
}

pipeline {
    agent any

    parameters {
        booleanParam(name: 'ROLLBACK', defaultValue: false, description: 'Bascule le trafic prod vers la couleur précédente au lieu de déployer')
    }

    environment {
        // NB: les credentials DB_PASS_* / API_HMAC_SECRET_PROD ne sont pas encore créées
        // dans Jenkins et ne sont utilisées par aucun stage pour l'instant -> retirées ici.
        // À réintégrer (credentials('db-pass-dev') etc.) le jour où un stage en a besoin.

        // --- Hooks de déploiement Render (les 4 credentials mises en place pour ce pipeline) ---
        RENDER_HOOK_DEV        = credentials('render-hook-dev')
        RENDER_HOOK_PREPROD    = credentials('render-hook-preprod')
        RENDER_HOOK_PROD_BLUE  = credentials('render-hook-prod-blue')
        RENDER_HOOK_PROD_GREEN = credentials('render-hook-prod-green')
    }

    stages {

        stage('Checkout') {
            steps {
                checkout scm
                script {
                    env.GIT_SHA = sh(script: 'git rev-parse --short HEAD', returnStdout: true).trim()
                    // Ce job n'est pas un "Multibranch Pipeline" : BRANCH_NAME n'existe pas
                    // nativement, on la reconstruit à partir de GIT_BRANCH (ex: "origin/develop")
                    // pour que les `when { branch '...' }` ci-dessous fonctionnent.
                    env.BRANCH_NAME = env.GIT_BRANCH?.replaceFirst(/^origin\//, '') ?: ''
                    echo "Branche détectée : ${env.BRANCH_NAME}"

                    def lastAuthor = sh(script: 'git log -1 --format=%an', returnStdout: true).trim()
                    if (lastAuthor == 'Jenkins CI') {
                        echo "Dernier commit (${env.GIT_SHA}) fait par Jenkins CI lui-même (bascule/rollback automatique) -> build ignoré pour éviter une boucle infinie de déclenchements."
                        env.SKIP_CI = 'true'
                    } else {
                        env.SKIP_CI = 'false'
                    }
                }
                // Identité Git locale au workspace, nécessaire pour les commits automatiques
                // (bascule active_color.txt / last_stable_tag.txt) plus bas dans le pipeline.
                sh 'git config user.email "ci@thetiptop.local"'
                sh 'git config user.name "Jenkins CI"'
            }
        }

        // ======================= TESTS : tous exécutés AVANT la construction de l'image =======================
        // Chaque stage = un type de test, visible séparément dans la Stage View. Les tests tournent dans des
        // conteneurs jetables (image d'outillage + MariaDB), jamais sur l'agent. Voir ci/run-suite.sh.

        stage('1. Qualité : lint PHP') {
            when { expression { return env.SKIP_CI != 'true' } }
            steps { sh 'sh ci/run-suite.sh lint' }
        }

        stage('2. Tests unitaires') {
            when { expression { return env.SKIP_CI != 'true' } }
            steps { sh 'sh ci/run-suite.sh unit' }
            post { always { script { publishReport('reports/unit.xml') } } }
        }

        stage("3. Tests d'intégration (MariaDB)") {
            when { expression { return env.SKIP_CI != 'true' } }
            steps { sh 'sh ci/run-suite.sh integration' }
            post { always { script { publishReport('reports/integration.xml') } } }
        }

        stage("4. Tests d'API (HMAC, anti-rejeu, rate limit)") {
            when { expression { return env.SKIP_CI != 'true' } }
            steps { sh 'sh ci/run-suite.sh api' }
            post { always { script { publishReport('reports/api.xml') } } }
        }

        stage('5. Tests de sécurité (SAST + secrets)') {
            when { expression { return env.SKIP_CI != 'true' } }
            steps {
                sh 'sh ci/run-suite.sh security'
                // Trivy : vulnérabilités, secrets et mauvaises configurations du dépôt.
                // Un résultat HIGH/CRITICAL marque le build "instable" sans l'arrêter (la base de
                // vulnérabilités est téléchargée à chaque exécution : on évite qu'un incident réseau bloque la chaîne).
                catchError(buildResult: 'SUCCESS', stageResult: 'UNSTABLE') {
                    sh 'sh ci/run-trivy.sh fs'
                }
            }
            post { always { script { publishReport('reports/security.xml') } } }
        }

        stage('6a. Tests end-to-end (parcours HTTP)') {
            when { expression { return env.SKIP_CI != 'true' } }
            steps { sh 'sh ci/run-suite.sh e2e' }
            post { always { script { publishReport('reports/e2e.xml') } } }
        }

        stage('6b. Tests end-to-end (navigateur Chromium)') {
            when { expression { return env.SKIP_CI != 'true' } }
            steps { sh 'sh ci/run-browser-tests.sh' }
            post { always { script { publishReport('reports/e2e-browser.xml') } } }
        }

        // ======================= BUILD : uniquement si TOUS les tests ci-dessus sont passés =======================

        stage('Build image Docker') {
            when { expression { return env.SKIP_CI != 'true' } }
            steps {
                sh "docker build -t thetiptop:${GIT_SHA} ."
                sh "docker tag thetiptop:${GIT_SHA} thetiptop:current"
            }
        }

        stage("Scan de l'image (Trivy)") {
            when { expression { return env.SKIP_CI != 'true' } }
            steps {
                catchError(buildResult: 'SUCCESS', stageResult: 'UNSTABLE') {
                    sh "sh ci/run-trivy.sh image thetiptop:${GIT_SHA}"
                }
            }
        }

        stage('Deploy DEV') {
            when {
                allOf {
                    branch 'develop'
                    expression { return env.SKIP_CI != 'true' }
                }
            }
            steps {
                sh 'curl -sf -X POST "$RENDER_HOOK_DEV"'
                // Attend que DEV serve CE commit (en-tête X-Release) puis exécute les tests de fumée.
                sh "sh ci/smoke-test.sh https://thetiptop-dev.onrender.com ${env.GIT_SHA}"
            }
        }

        stage('Test de performance (k6) sur DEV') {
            when {
                allOf {
                    branch 'develop'
                    expression { return env.SKIP_CI != 'true' }
                }
            }
            steps {
                // Seuils : < 1 % d'erreurs et p95 < 2 s. Dépassement = build instable (pas bloquant).
                catchError(buildResult: 'SUCCESS', stageResult: 'UNSTABLE') {
                    sh 'sh ci/run-perf.sh https://thetiptop-dev.onrender.com'
                }
            }
        }

        stage('Deploy PREPROD') {
            when {
                allOf {
                    branch 'preprod'
                    expression { return env.SKIP_CI != 'true' }
                }
            }
            steps {
                sh 'curl -sf -X POST "$RENDER_HOOK_PREPROD"'
                sh "sh ci/smoke-test.sh https://thetiptop-preprod.onrender.com ${env.GIT_SHA}"
            }
        }

        stage('Approbation manuelle PROD') {
            when {
                allOf {
                    branch 'master'
                    not { expression { return params.ROLLBACK } }
                    expression { return env.SKIP_CI != 'true' }
                }
            }
            steps {
                input message: "Déployer ${env.GIT_SHA} en production ?", ok: 'Déployer'
            }
        }

        stage('Deploy PROD (blue/green)') {
            when {
                allOf {
                    branch 'master'
                    not { expression { return params.ROLLBACK } }
                    expression { return env.SKIP_CI != 'true' }
                }
            }
            steps {
                script {
                    def active   = readFile('active_color.txt').trim()
                    def inactive = (active == 'blue') ? 'green' : 'blue'
                    def inactiveHook = (inactive == 'green') ? env.RENDER_HOOK_PROD_GREEN : env.RENDER_HOOK_PROD_BLUE
                    // 'blue' = service Render "thetiptop-blue", 'green' = "thetiptop-green"
                    def inactiveUrl  = (inactive == 'green') ? 'https://thetiptop-green.onrender.com' : 'https://thetiptop-blue.onrender.com'

                    echo "Couleur active actuelle : ${active} — déploiement de ${env.GIT_SHA} sur ${inactive}"

                    // 1. On déploie la nouvelle version sur la couleur INACTIVE, sans toucher
                    //    au trafic en cours (qui continue de servir `active`).
                    sh "curl -sf -X POST '${inactiveHook}'"
                    // Les tests de fumée attendent que la couleur inactive serve bien CE commit, puis
                    // vérifient pages, API, en-têtes. En cas d'échec, le trafic n'est PAS basculé.
                    sh "sh ci/smoke-test.sh ${inactiveUrl} ${env.GIT_SHA}"

                    // 2. Bascule du trafic : la couleur qui vient d'être validée devient active.
                    //    L'ancienne couleur active reste déployée telle quelle : c'est notre
                    //    filet de sécurité pour un rollback instantané (pas de redéploiement).
                    writeFile file: 'active_color.txt', text: inactive
                    writeFile file: 'last_stable_tag.txt', text: env.GIT_SHA
                    sh """
                        git add active_color.txt last_stable_tag.txt
                        git commit -m "prod: bascule ${active} -> ${inactive} (${env.GIT_SHA})"
                    """
                    // Le remote "origin" du workspace Jenkins pointe déjà vers GitHub (checkout
                    // en lecture anonyme) mais push nécessite un token -> credential dédiée.
                    // fetch + rebase juste avant le push : évite l'échec si quelqu'un (toi en
                    // local, ou un autre build) a poussé sur master entre-temps.
                    withCredentials([usernamePassword(credentialsId: 'github-push-token', usernameVariable: 'GH_USER', passwordVariable: 'GH_TOKEN')]) {
                        sh '''
                            git fetch origin master
                            git rebase origin/master
                            git push https://$GH_USER:$GH_TOKEN@github.com/ayoutech/thetiptop.git HEAD:master
                        '''
                    }
                }
                // --- Poussée des métriques DORA vers Prometheus Pushgateway ---
                // Pushgateway accessible via le nom du conteneur Docker sur le même
                // réseau (furious-network) : "pushgateway", pas un hostname VM externe.
                //
                // NB1: le terminateur "EOF" du heredoc doit être collé à la marge gauche
                // (sans indentation), sinon bash ne le reconnaît pas comme fin de bloc et
                // le mot "EOF" lui-même est envoyé comme une ligne supplémentaire au
                // pushgateway, ce qui casse le parsing du format Prometheus.
                //
                // NB2: on pousse sur un groupe "instance/${GIT_SHA}" distinct à chaque
                // déploiement, car le Pushgateway REMPLACE (il ne cumule jamais) la valeur
                // d'une métrique déjà poussée dans le même groupe job/instance. Sans ce
                // label unique, deployments_total resterait bloqué à "1" pour toujours,
                // quel que soit le nombre réel de déploiements. Avec un groupe par commit,
                // chaque déploiement crée sa propre série, et la fréquence de déploiement
                // (DORA) se calcule dans Prometheus avec :
                //   count(deployments_total{env="prod",status="success"})
                sh '''
LEAD_TIME=$(( $(date +%s) - $(git log -1 --format=%ct ${GIT_SHA}) ))
cat <<EOF | curl --data-binary @- http://pushgateway:9091/metrics/job/dora/env/prod/instance/${GIT_SHA} || true
# TYPE deployments_total counter
deployments_total{env="prod",status="success"} 1
# TYPE lead_time_seconds gauge
lead_time_seconds{env="prod"} ${LEAD_TIME}
EOF
'''
            }
        }

        stage('Rollback PROD (bascule instantanée)') {
            when {
                allOf {
                    expression { return params.ROLLBACK == true }
                    expression { return env.SKIP_CI != 'true' }
                }
            }
            steps {
                script {
                    def active   = readFile('active_color.txt').trim()
                    def previous = (active == 'blue') ? 'green' : 'blue'

                    echo "Rollback : bascule du trafic prod de ${active} vers ${previous} (${previous} sert toujours la dernière version stable connue : ${readFile('last_stable_tag.txt').trim()})"

                    // Pas de redéploiement : la couleur `previous` n'a jamais été touchée par
                    // le déploiement en échec, elle tourne déjà la version stable précédente.
                    // Le rollback consiste uniquement à re-basculer le trafic vers elle.
                    writeFile file: 'active_color.txt', text: previous
                    sh """
                        git add active_color.txt
                        git commit -m "rollback prod: bascule ${active} -> ${previous}"
                    """
                    withCredentials([usernamePassword(credentialsId: 'github-push-token', usernameVariable: 'GH_USER', passwordVariable: 'GH_TOKEN')]) {
                        sh '''
                            git fetch origin master
                            git rebase origin/master
                            git push https://$GH_USER:$GH_TOKEN@github.com/ayoutech/thetiptop.git HEAD:master
                        '''
                    }
                }
                // Groupe distinct "-rollback" pour ne pas écraser le groupe de succès du
                // même commit (même raison qu'au-dessus : un groupe = une série Prometheus).
                sh '''
cat <<EOF | curl --data-binary @- http://pushgateway:9091/metrics/job/dora/env/prod/instance/${GIT_SHA}-rollback || true
# TYPE deployments_failed_total counter
deployments_failed_total{env="prod"} 1
EOF
'''
            }
        }
    }

    post {
        failure {
            echo "Pipeline en échec pour ${env.GIT_SHA} — voir les logs Jenkins."
        }
    }
}
