pipeline {
    agent any

    parameters {
        string(name: 'SITE_NAME', defaultValue: params.SITE_NAME ?: null, description: 'Site name')
        string(name: 'HCAPTCHA_SITEKEY', defaultValue: params.HCAPTCHA_SITEKEY ?: null, description: 'Hcaptcha site key')
        string(name: 'HCAPTCHA_SECRET', defaultValue: params.HCAPTCHA_SECRET ?: null, description: 'Hcaptcha secret')
    }

    stages {
        stage('Build') {
            steps {
                sh 'docker compose build'
            }
        }
        stage('Deploy') {
            steps {
                sh 'docker compose up --remove-orphans -d'
            }
        }
    }
}
