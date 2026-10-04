// Jenkinsfile — Thé Tip Top
// Pipeline : develop (auto) -> preprod (auto) -> master (validation humaine + blue/green sur Render)
// Les secrets (hooks Render) sont TOUJOURS lus via Jenkins Credentials, jamais codés en dur.
//
// Traçabilité blue/green :
//   - active_color.txt   : couleur actuellement servie en prod (blue|green), versionnée dans le repo.
//     'blue' = service Render "thetiptop", 'green' = service Render "thetiptop-green".
//     Le reverse proxy / la config DNS de thetiptop.onrender.com doit lire cette valeur pour
//     savoir vers quel service router le trafic.
//   - last_stable_tag.txt: dernier commit déployé avec succès et validé en prod (traçabilité DORA).
//
// NB: l'agent Jenkins n'a pas PHP installé -> toutes les commandes php (lint, tests) tournent
// à l'intérieur de l'image Docker du projet via `docker run`, jamais directement sur l'agent.

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
                }
                // Identité Git locale au workspace, nécessaire pour les commits automatiques
                // (bascule active_color.txt / last_stable_tag.txt) plus bas dans le pipeline.
                sh 'git config user.email "ci@thetiptop.local"'
                sh 'git config user.name "Jenkins CI"'
            }
        }

        stage('Build image Docker') {
            steps {
                sh "docker build -t thetiptop:${GIT_SHA} ."
                sh "docker tag thetiptop:${GIT_SHA} thetiptop:current"
            }
        }

        stage('Lint & Tests') {
            // Tout tourne DANS le conteneur (qui contient PHP), jamais sur l'agent Jenkins.
            steps {
                sh "docker run --rm thetiptop:${GIT_SHA} php -l /var/www/html/index.php"
                sh """
                    docker run --rm thetiptop:${GIT_SHA} sh -c '
                        for f in \$(find /var/www/html -name "*.php"); do php -l "\$f" || exit 1; done
                    '
                """
                sh "docker run --rm thetiptop:${GIT_SHA} php tests/run-http-tests.php || true"
            }
        }

        stage('Deploy DEV') {
            when { branch 'develop' }
            steps {
                sh 'curl -sf -X POST "$RENDER_HOOK_DEV"'
                sh 'sleep 15 && curl -sf https://thetiptop-dev.onrender.com/healthz'
            }
        }

        stage('Deploy PREPROD') {
            when { branch 'preprod' }
            steps {
                sh 'curl -sf -X POST "$RENDER_HOOK_PREPROD"'
                sh 'sleep 15 && curl -sf https://thetiptop-preprod.onrender.com/healthz'
                sh "docker run --rm thetiptop:${GIT_SHA} php tests/run-http-tests.php --target=preprod || true"
            }
        }

        stage('Approbation manuelle PROD') {
            when {
                branch 'master'
                not { expression { return params.ROLLBACK } }
            }
            steps {
                input message: "Déployer ${env.GIT_SHA} en production ?", ok: 'Déployer'
            }
        }

        stage('Deploy PROD (blue/green)') {
            when {
                branch 'master'
                not { expression { return params.ROLLBACK } }
            }
            steps {
                script {
                    def active   = readFile('active_color.txt').trim()
                    def inactive = (active == 'blue') ? 'green' : 'blue'
                    def inactiveHook = (inactive == 'green') ? env.RENDER_HOOK_PROD_GREEN : env.RENDER_HOOK_PROD_BLUE
                    // 'blue' = service Render "thetiptop" (prod historique), 'green' = "thetiptop-green"
                    def inactiveUrl  = (inactive == 'green') ? 'https://thetiptop-green.onrender.com' : 'https://thetiptop.onrender.com'

                    echo "Couleur active actuelle : ${active} — déploiement de ${env.GIT_SHA} sur ${inactive}"

                    // 1. On déploie la nouvelle version sur la couleur INACTIVE, sans toucher
                    //    au trafic en cours (qui continue de servir `active`).
                    sh "curl -sf -X POST '${inactiveHook}'"
                    sh "sleep 20 && curl -sf ${inactiveUrl}/healthz"
                    sh "docker run --rm thetiptop:${GIT_SHA} php tests/run-http-tests.php --target=${inactiveUrl} || true"

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
                // NB: le terminateur "EOF" du heredoc doit être collé à la marge gauche
                // (sans indentation), sinon bash ne le reconnaît pas comme fin de bloc et
                // le mot "EOF" lui-même est envoyé comme une ligne supplémentaire au
                // pushgateway, ce qui casse le parsing du format Prometheus.
                sh '''
LEAD_TIME=$(( $(date +%s) - $(git log -1 --format=%ct) ))
cat <<EOF | curl --data-binary @- http://pushgateway:9091/metrics/job/dora/env/prod || true
# TYPE deployments_total counter
deployments_total{env="prod",status="success"} 1
# TYPE lead_time_seconds gauge
lead_time_seconds{env="prod"} ${LEAD_TIME}
EOF
'''
            }
        }

        stage('Rollback PROD (bascule instantanée)') {
            when { expression { return params.ROLLBACK == true } }
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
                sh '''
cat <<EOF | curl --data-binary @- http://pushgateway:9091/metrics/job/dora/env/prod || true
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
