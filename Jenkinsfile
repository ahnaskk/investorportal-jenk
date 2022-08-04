pipeline {
  agent any

  tools {nodejs "nodejs"}

  stages {
    stage('Test') {
      steps {
        sh 'node -v'
        sh 'npm -v'
        sh 'npm install'
        sh 'php composer.phar install'
        sh 'phpunit'
      }
    }
  }
}
