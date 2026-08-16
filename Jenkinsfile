    agent any

    environment {

        AWS_REGION = 'us-east-1'

        ECR_REPO = 'employee-app'

        KUBECONFIG = '/var/lib/jenkins/.kube/config'

        DB_HOST = 'employee-db.c6bwoikgyoks.us-east-1.rds.amazonaws.com'

        DB_USER = 'admin'

        DB_NAME = 'employee_db'
    }

    stages {

        stage('Checkout') {
            steps {
                checkout scm
            }
        }


        stage('AWS Account') {
            steps {
                script {

                    env.AWS_ACCOUNT_ID = sh(
                        script: 'aws sts get-caller-identity --query Account --output text',
                        returnStdout: true
                    ).trim()

                    env.ECR_REGISTRY =
                        "${env.AWS_ACCOUNT_ID}.dkr.ecr.${env.AWS_REGION}.amazonaws.com"

                    echo "AWS Account ID: ${env.AWS_ACCOUNT_ID}"
                    echo "ECR Registry: ${env.ECR_REGISTRY}"
                }
            }
        }


	stage('Docker Build') {
            steps {

                sh '''
                docker build \
                -t ${ECR_REPO}:${BUILD_NUMBER} .
                '''
            }
        }


        stage('ECR Login') {
            steps {

                sh '''
                aws ecr get-login-password \
                --region ${AWS_REGION} | \
                docker login \
                --username AWS \
                --password-stdin ${ECR_REGISTRY}
                '''
            }
        }


        stage('Push Image') {
            steps {

                sh '''
                docker tag \
                ${ECR_REPO}:${BUILD_NUMBER} \
                ${ECR_REGISTRY}/${ECR_REPO}:${BUILD_NUMBER}

                docker push \
                ${ECR_REGISTRY}/${ECR_REPO}:${BUILD_NUMBER}
                '''
            }
        }


	stage('ECR Secret') {
            steps {

                sh '''
                kubectl create secret docker-registry ecr-secret \
                --docker-server=${ECR_REGISTRY} \
                --docker-username=AWS \
                --docker-password="$(aws ecr get-login-password --region ${AWS_REGION})" \
                --dry-run=client \
                -o yaml | kubectl apply -f -
                '''
            }
        }


        stage('Deploy') {
            steps {

                withCredentials([
                    string(
                        credentialsId: 'rds-password',
                        variable: 'DB_PASSWORD'
                    )
                ]) {

                    sh '''
                    kubectl create secret generic db-secret \
                    --from-literal=password="$DB_PASSWORD" \
                    --dry-run=client \
                    -o yaml | kubectl apply -f -

                    helm upgrade --install employee-app \
                    helm/employee-app \
                    --set image.repository=${ECR_REGISTRY}/${ECR_REPO} \
                    --set image.tag=${BUILD_NUMBER} \
                    --set db.host=${DB_HOST} \
                    --set db.user=${DB_USER} \
                    --set db.name=${DB_NAME}
                    '''
                }
            }
        }


        stage('Verify') {
            steps {

                sh '''
                kubectl get pods
                kubectl get svc
                '''
            }
        }
    }
}
