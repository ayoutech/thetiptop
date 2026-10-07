#!/bin/sh
# Crée (ou met à jour) les 7 pipelines de test dans Jenkins via son API.
#   Usage : JENKINS_URL=http://localhost:8080 JENKINS_USER=furiousducks JENKINS_TOKEN=xxxx sh ci/jenkins/create-jobs.sh
# Le jeton se crée dans Jenkins : utilisateur > Configure > API Token.
set -eu
: "${JENKINS_URL:?}" "${JENKINS_USER:?}" "${JENKINS_TOKEN:?}"
REPO="${REPO_URL:-https://github.com/ayoutech/thetiptop.git}"
DIR="$(cd "$(dirname "$0")" && pwd)"
while IFS='|' read -r JOB KEY; do
  [ -z "$JOB" ] && continue
  CFG=$(mktemp)
  cat > "$CFG" <<EOF
<?xml version='1.1' encoding='UTF-8'?>
<flow-definition plugin="workflow-job">
  <description>Pipeline de test dédié : $KEY (lancé par le pipeline principal thetiptop-pipeline)</description>
  <keepDependencies>false</keepDependencies>
  <properties>
    <hudson.model.ParametersDefinitionProperty>
      <parameterDefinitions>
        <hudson.model.StringParameterDefinition>
          <name>BRANCH</name>
          <defaultValue>develop</defaultValue>
        </hudson.model.StringParameterDefinition>
      </parameterDefinitions>
    </hudson.model.ParametersDefinitionProperty>
  </properties>
  <definition class="org.jenkinsci.plugins.workflow.cps.CpsScmFlowDefinition" plugin="workflow-cps">
    <scm class="hudson.plugins.git.GitSCM" plugin="git">
      <configVersion>2</configVersion>
      <userRemoteConfigs><hudson.plugins.git.UserRemoteConfig><url>$REPO</url></hudson.plugins.git.UserRemoteConfig></userRemoteConfigs>
      <branches><hudson.plugins.git.BranchSpec><name>*/\${BRANCH}</name></hudson.plugins.git.BranchSpec></branches>
      <doGenerateSubmoduleConfigurations>false</doGenerateSubmoduleConfigurations>
      <submoduleCfg class="empty-list"/>
      <extensions/>
    </scm>
    <scriptPath>ci/jenkins/Jenkinsfile.$KEY</scriptPath>
    <lightweight>false</lightweight>
  </definition>
  <triggers/>
  <disabled>false</disabled>
</flow-definition>
EOF
  CODE=$(curl -s -o /dev/null -w '%{http_code}' -u "$JENKINS_USER:$JENKINS_TOKEN" "$JENKINS_URL/job/$JOB/api/json")
  if [ "$CODE" = "200" ]; then
    R=$(curl -s -o /dev/null -w '%{http_code}' -u "$JENKINS_USER:$JENKINS_TOKEN" -H 'Content-Type: application/xml' --data-binary @"$CFG" "$JENKINS_URL/job/$JOB/config.xml"); ACT="mis à jour"
  else
    R=$(curl -s -o /dev/null -w '%{http_code}' -u "$JENKINS_USER:$JENKINS_TOKEN" -H 'Content-Type: application/xml' --data-binary @"$CFG" "$JENKINS_URL/createItem?name=$JOB"); ACT="créé"
  fi
  rm -f "$CFG"
  echo "$JOB : $ACT (HTTP $R)"
done < "$DIR/jobs.txt"
