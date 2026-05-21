pipeline {
    agent any

    environment {
        REGISTRY = 'docker.io/username'
        IMAGE_NAMESPACE = 'catering-markesot'
        STACK_NAME = 'catering'
        DEPLOY_PATH = '/opt/catering-markesot'
        DEPLOY_HOST = 'deploy@your-vps-ip'
        APP_IMAGE = "${REGISTRY}/${IMAGE_NAMESPACE}-app:${BUILD_NUMBER}"
        WEB_IMAGE = "${REGISTRY}/${IMAGE_NAMESPACE}-web:${BUILD_NUMBER}"
        APP_ENV_CONFIG = "app_env_${BUILD_NUMBER}"
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Build Images') {
            steps {
                sh 'docker build --target app -t $APP_IMAGE .'
                sh 'docker build --target web -t $WEB_IMAGE .'
            }
        }

        stage('Smoke Test Image') {
            steps {
                sh 'docker run --rm --entrypoint php $APP_IMAGE -v'
            }
        }

        stage('Push Images') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'docker-registry', usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASSWORD')]) {
                    sh 'echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USER" --password-stdin ${REGISTRY}'
                    sh 'docker push $APP_IMAGE'
                    sh 'docker push $WEB_IMAGE'
                }
            }
        }

        stage('Deploy Swarm') {
            steps {
                sshagent(credentials: ['vps-ssh-key']) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${DEPLOY_HOST} '
                            cd ${DEPLOY_PATH} &&
                            docker pull ${APP_IMAGE} &&
                            docker pull ${WEB_IMAGE} &&
                            (docker config inspect ${APP_ENV_CONFIG} >/dev/null 2>&1 || docker config create ${APP_ENV_CONFIG} .env >/dev/null) &&
                            set -a && . ./.env && set +a &&
                            APP_IMAGE=${APP_IMAGE} WEB_IMAGE=${WEB_IMAGE} APP_ENV_CONFIG=${APP_ENV_CONFIG} docker stack deploy -c docker-stack.yml ${STACK_NAME}
                        '
                    """
                }
            }
        }
    }

    post {
        always {
            sh 'docker logout ${REGISTRY} || true'
        }
    }
}
