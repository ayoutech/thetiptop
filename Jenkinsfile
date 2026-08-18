pipeline {
    agent any

    environment {
        IMAGE_NAME = "thetiptop"
        CONTAINER_NAME = "thetiptop_app"
        NETWORK_NAME = "furious-network"
    }

    stages {
        stage('Checkout') {
            steps {
                echo 'Récupération du code depuis Gitea...'
                checkout scm
            }
        }

        stage('Build') {
            steps {
                echo 'Construction de l image Docker...'
                sh 'docker build -t ${IMAGE_NAME}:latest .'
            }
        }

        stage('Test') {
            steps {
                echo 'Exécution des tests...'
                sh 'docker run --rm ${IMAGE_NAME}:latest php -l src/index.php'
                sh 'docker run --rm ${IMAGE_NAME}:latest php -l src/config/database.php'
            }
        }

        stage('Deploy Dev') {
            steps {
                echo 'Déploiement en environnement de développement...'
                sh '''
                    docker stop ${CONTAINER_NAME} || true
                    docker rm ${CONTAINER_NAME} || true
                    docker-compose up -d
                '''
            }
        }

        stage('Health Check') {
            steps {
                echo 'Vérification que le site répond...'
                sh 'sleep 5 && curl -f http://localhost:8080 || exit 1'
            }
        }
    }

    post {
        success {
            echo 'Déploiement réussi !'
        }
        failure {
            echo 'Échec du pipeline. Vérifiez les logs.'
        }
    }
}
