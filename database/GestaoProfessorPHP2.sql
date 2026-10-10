-- MySQL dump 10.13  Distrib 8.4.10, for Linux (x86_64)
--
-- Host: localhost    Database: GestaoProfessor
-- ------------------------------------------------------
-- Server version	8.4.10

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Campo`
--

DROP TABLE IF EXISTS `Campo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Campo` (
  `codigo` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(60) NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Campo`
--

LOCK TABLES `Campo` WRITE;
/*!40000 ALTER TABLE `Campo` DISABLE KEYS */;
INSERT INTO `Campo` VALUES (27,'Eletricidade'),(28,'Informatica'),(29,'Gestao'),(30,'Construcao civil');
/*!40000 ALTER TABLE `Campo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Classificacao`
--

DROP TABLE IF EXISTS `Classificacao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Classificacao` (
  `cod_Campo` int NOT NULL,
  `cod_Qualificacao` int NOT NULL,
  PRIMARY KEY (`cod_Campo`,`cod_Qualificacao`),
  KEY `fk_cod_Qualificacao` (`cod_Qualificacao`),
  CONSTRAINT `fk_cod_Qualificacao` FOREIGN KEY (`cod_Qualificacao`) REFERENCES `Qualificacao` (`cod_Quali`),
  CONSTRAINT `fk_codigo_campo` FOREIGN KEY (`cod_Campo`) REFERENCES `Campo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Classificacao`
--

LOCK TABLES `Classificacao` WRITE;
/*!40000 ALTER TABLE `Classificacao` DISABLE KEYS */;
INSERT INTO `Classificacao` VALUES (27,18),(28,19),(28,20),(27,21);
/*!40000 ALTER TABLE `Classificacao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Coordenador`
--

DROP TABLE IF EXISTS `Coordenador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Coordenador` (
  `cod_Formador` int NOT NULL,
  PRIMARY KEY (`cod_Formador`),
  CONSTRAINT `fk_cod_Formador` FOREIGN KEY (`cod_Formador`) REFERENCES `Formador` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Coordenador`
--

LOCK TABLES `Coordenador` WRITE;
/*!40000 ALTER TABLE `Coordenador` DISABLE KEYS */;
INSERT INTO `Coordenador` VALUES (14),(16);
/*!40000 ALTER TABLE `Coordenador` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Diretor_Turma`
--

DROP TABLE IF EXISTS `Diretor_Turma`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Diretor_Turma` (
  `cod_Formador` int NOT NULL,
  PRIMARY KEY (`cod_Formador`),
  CONSTRAINT `Diretor_Turma_ibfk_1` FOREIGN KEY (`cod_Formador`) REFERENCES `Formador` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Diretor_Turma`
--

LOCK TABLES `Diretor_Turma` WRITE;
/*!40000 ALTER TABLE `Diretor_Turma` DISABLE KEYS */;
INSERT INTO `Diretor_Turma` VALUES (14),(17);
/*!40000 ALTER TABLE `Diretor_Turma` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Formador`
--

DROP TABLE IF EXISTS `Formador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Formador` (
  `codigo` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(40) NOT NULL,
  `apelido` varchar(40) NOT NULL,
  `email` varchar(40) NOT NULL,
  `genero` varchar(40) NOT NULL,
  `estadoCivil` varchar(40) NOT NULL,
  `contacto` int NOT NULL,
  `salario` int NOT NULL,
  `valor_hora` int NOT NULL,
  `horas_mes` int NOT NULL,
  PRIMARY KEY (`codigo`),
  UNIQUE KEY `contacto_Chave_candidata` (`contacto`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Formador`
--

LOCK TABLES `Formador` WRITE;
/*!40000 ALTER TABLE `Formador` DISABLE KEYS */;
INSERT INTO `Formador` VALUES (14,'Malik','mangue','mlktecno@gmail.com','Feminino','Divorciado(a)',934323163,1000,100,5),(16,'Allen','Dinis','Kenny@gmail.com','Masculino','Solteiro(a)',871234565,2000,20,50),(17,'Edmundo','Mapotere','fedmundo@gmail.com','Masculino','Solteiro(a)',846533794,400000,3000,7),(18,'Keany','Pessula','keanypessula@gmail.com','Masculino','Solteiro(a)',844431730,50000,600,60);
/*!40000 ALTER TABLE `Formador` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Formando`
--

DROP TABLE IF EXISTS `Formando`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Formando` (
  `codigo_formando` int NOT NULL AUTO_INCREMENT,
  `nome_formando` varchar(100) NOT NULL,
  `apelido_formando` varchar(100) NOT NULL,
  `contacto_formando` int DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `BI` varchar(20) NOT NULL,
  PRIMARY KEY (`codigo_formando`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Formando`
--

LOCK TABLES `Formando` WRITE;
/*!40000 ALTER TABLE `Formando` DISABLE KEYS */;
INSERT INTO `Formando` VALUES (9,'malik','malik',0,'11111','mlktecno@gmail.com');
/*!40000 ALTER TABLE `Formando` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Inscricao`
--

DROP TABLE IF EXISTS `Inscricao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Inscricao` (
  `codigo_inscricao` int NOT NULL AUTO_INCREMENT,
  `codigo_formando` int NOT NULL,
  `codigo_modulo` int NOT NULL,
  `semestre` varchar(20) NOT NULL DEFAULT '1º Semestre',
  `data_inscricao` date DEFAULT NULL,
  PRIMARY KEY (`codigo_inscricao`),
  KEY `fk_inscricao_formando` (`codigo_formando`),
  KEY `fk_inscricao_modulo` (`codigo_modulo`),
  CONSTRAINT `fk_inscricao_formando` FOREIGN KEY (`codigo_formando`) REFERENCES `Formando` (`codigo_formando`),
  CONSTRAINT `fk_inscricao_modulo` FOREIGN KEY (`codigo_modulo`) REFERENCES `Modulo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Inscricao`
--

LOCK TABLES `Inscricao` WRITE;
/*!40000 ALTER TABLE `Inscricao` DISABLE KEYS */;
/*!40000 ALTER TABLE `Inscricao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Licao`
--

DROP TABLE IF EXISTS `Licao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Licao` (
  `codigo` int NOT NULL AUTO_INCREMENT,
  `cod_Modulo` int NOT NULL,
  `cod_Formador` int NOT NULL,
  `cod_Sala` int NOT NULL,
  `cod_Turma` int NOT NULL,
  `data` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `fk_licao_formador` (`cod_Formador`),
  KEY `fk_licao_modulo` (`cod_Modulo`),
  KEY `fk_licao_sala` (`cod_Sala`),
  KEY `fk_licao_turma` (`cod_Turma`),
  CONSTRAINT `fk_licao_formador` FOREIGN KEY (`cod_Formador`) REFERENCES `Formador` (`codigo`),
  CONSTRAINT `fk_licao_modulo` FOREIGN KEY (`cod_Modulo`) REFERENCES `Modulo` (`codigo`),
  CONSTRAINT `fk_licao_sala` FOREIGN KEY (`cod_Sala`) REFERENCES `Sala` (`codigo`),
  CONSTRAINT `fk_licao_turma` FOREIGN KEY (`cod_Turma`) REFERENCES `Turma` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Licao`
--

LOCK TABLES `Licao` WRITE;
/*!40000 ALTER TABLE `Licao` DISABLE KEYS */;
/*!40000 ALTER TABLE `Licao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Log`
--

DROP TABLE IF EXISTS `Log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Log` (
  `codigo` int NOT NULL AUTO_INCREMENT,
  `id_Usuario` int NOT NULL,
  `acao` varchar(100) NOT NULL,
  `descricao` varchar(200) NOT NULL,
  `data` datetime NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `fk_id_Usuario` (`id_Usuario`),
  CONSTRAINT `fk_id_Usuario` FOREIGN KEY (`id_Usuario`) REFERENCES `Usuario` (`idUser`)
) ENGINE=InnoDB AUTO_INCREMENT=326 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Log`
--

LOCK TABLES `Log` WRITE;
/*!40000 ALTER TABLE `Log` DISABLE KEYS */;
INSERT INTO `Log` VALUES (200,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-02 19:20:52'),(201,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-02 19:50:41'),(202,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-02 20:04:18'),(203,64,'INSERT','Pessula Kenny foi cadastrado','2026-10-02 20:25:36'),(204,64,'INSERT','Mangue Edson foi cadastrado','2026-10-02 20:27:07'),(205,64,'INSERT','Kenny Pessula foi cadastrado','2026-10-02 20:36:41'),(206,65,'LOGIN','Utilizador KPessula iniciou sessão','2026-10-02 20:38:25'),(207,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-03 11:47:23'),(208,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-06 09:25:06'),(209,64,'INSERT','mini Operador foi cadastrado','2026-10-06 09:49:50'),(210,69,'LOGIN','Utilizador OMini iniciou sessão','2026-10-06 09:51:25'),(211,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-06 09:52:25'),(212,64,'UPDATE','Senha do utilizador OMini (ID: 69) foi resetada','2026-10-06 09:55:56'),(213,69,'LOGIN','Utilizador OMini iniciou sessão','2026-10-06 09:56:39'),(214,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-06 10:24:57'),(215,64,'INSERT','Diretor de Turma Malik foi cadastrado','2026-10-06 10:44:10'),(216,64,'INSERT','Coordenador Malik foi cadastrado','2026-10-06 10:45:05'),(217,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-06 11:01:20'),(218,69,'LOGIN','Utilizador OMini iniciou sessão','2026-10-06 11:01:59'),(219,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-06 11:03:14'),(220,64,'DELETE','Utilizador (ID: 66) foi removido','2026-10-06 11:12:11'),(221,64,'INSERT','super Admin foi cadastrado','2026-10-06 11:14:02'),(222,64,'DELETE','Utilizador (ID: 67) foi removido','2026-10-06 11:14:37'),(223,64,'INSERT','auditor auditor foi cadastrado','2026-10-06 12:18:05'),(224,71,'LOGIN','Utilizador auditor iniciou sessão','2026-10-06 12:18:17'),(225,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-06 14:24:09'),(226,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-07 09:01:12'),(227,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-07 11:41:33'),(228,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-07 14:42:04'),(229,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-08 08:59:49'),(230,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-08 11:43:18'),(231,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-08 15:05:07'),(232,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-09 09:25:39'),(233,64,'INSERT','Classificação  foi cadastrada','2026-10-09 09:55:13'),(234,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-09 11:26:55'),(235,64,'INSERT','Turma TPW-1 foi cadastrada','2026-10-09 12:39:07'),(236,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-09 13:59:18'),(237,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-09 14:27:25'),(238,64,'INSERT','Mangue Edson foi cadastrado','2026-10-09 14:41:34'),(239,64,'UPDATE','Utilizador KPessula (ID: 65) foi atualizado','2026-10-09 14:42:50'),(240,72,'LOGIN','Utilizador EMangue iniciou sessão','2026-10-09 14:48:19'),(241,64,'UPDATE','Utilizador EMapotere (ID: 70) foi atualizado','2026-10-09 14:52:21'),(242,64,'UPDATE','Utilizador admin (ID: 64) foi atualizado','2026-10-09 14:54:48'),(243,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-09 14:57:14'),(244,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-09 14:59:03'),(245,64,'UPDATE','Senha do utilizador KPessula (ID: 65) foi resetada','2026-10-09 14:59:55'),(246,65,'LOGIN','Utilizador KPessula iniciou sessão','2026-10-09 15:00:43'),(247,65,'UPDATE','Senha do utilizador KPessula (ID: 65) foi alterada','2026-10-09 15:01:06'),(248,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-09 15:10:40'),(249,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-09 15:14:32'),(250,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-09 15:15:21'),(251,64,'UPDATE','Senha do utilizador EMangue (ID: 72) foi resetada','2026-10-09 15:15:43'),(252,72,'LOGIN','Utilizador EMangue iniciou sessão','2026-10-09 15:16:31'),(253,72,'UPDATE','Senha do utilizador EMangue (ID: 72) foi alterada','2026-10-09 15:17:09'),(254,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-09 15:19:54'),(255,64,'UPDATE','Senha do utilizador EMangue (ID: 72) foi resetada','2026-10-09 15:20:08'),(256,72,'LOGIN','Utilizador EMangue iniciou sessão','2026-10-09 15:20:17'),(257,72,'UPDATE','Senha do utilizador EMangue (ID: 72) foi alterada','2026-10-09 15:20:30'),(258,72,'UPDATE','Formador Malik mangue (ID: 14) foi atualizado','2026-10-09 15:21:46'),(259,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-09 15:22:37'),(260,72,'INSERT','Matrícula para a data 2026-10-22 foi cadastrada','2026-10-09 16:07:34'),(261,72,'DELETE','Matrícula (ID: 13) da data 2026-10-22 foi removida','2026-10-09 16:07:49'),(262,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-09 17:33:15'),(263,70,'LOGIN','Utilizador EMapotere iniciou sessão','2026-10-09 17:50:43'),(264,70,'UPDATE','Senha do utilizador EMapotere (ID: 70) foi alterada','2026-10-09 17:51:04'),(265,70,'INSERT','Formador Kenny Pessula foi cadastrado','2026-10-09 17:52:37'),(266,70,'INSERT','Turma Tpw-1 foi cadastrada','2026-10-09 17:54:49'),(267,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-09 18:06:42'),(268,64,'INSERT','Mapotere Fernando foi cadastrado','2026-10-09 18:09:21'),(269,73,'LOGIN','Utilizador fernando iniciou sessão','2026-10-09 18:09:42'),(270,73,'UPDATE','Senha do utilizador fernando (ID: 73) foi alterada','2026-10-09 18:10:21'),(271,73,'LOGIN','Utilizador fernando iniciou sessão','2026-10-09 18:10:35'),(272,73,'UPDATE','Formador Allen Dinis (ID: 16) foi atualizado','2026-10-09 18:11:11'),(273,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-09 18:12:19'),(274,73,'UPDATE','Formador Allen Dinis (ID: 16) foi atualizado','2026-10-09 18:13:00'),(275,64,'UPDATE','Senha do utilizador EMapotere (ID: 70) foi resetada','2026-10-09 18:14:15'),(276,70,'LOGIN','Utilizador EMapotere iniciou sessão','2026-10-09 18:14:33'),(277,70,'UPDATE','Senha do utilizador EMapotere (ID: 70) foi alterada','2026-10-09 18:14:49'),(278,73,'INSERT','Formador Edmundo Mapotere foi cadastrado','2026-10-09 18:15:25'),(279,70,'INSERT','Formando j j foi cadastrado','2026-10-09 18:16:34'),(280,73,'INSERT','Matrícula para a data 2026-10-22 foi cadastrada','2026-10-09 18:18:38'),(281,73,'UPDATE','Matrícula (ID: 14) para a data 2026-10-07 foi atualizada','2026-10-09 18:19:05'),(282,73,'INSERT','Nível Cv4 foi cadastrado','2026-10-09 18:20:01'),(283,73,'INSERT','Campo Informatica foi cadastrado','2026-10-09 18:20:53'),(284,73,'UPDATE','Campo Eletricidade (ID: 27) foi atualizado','2026-10-09 18:21:24'),(285,73,'DELETE','Sala (ID: 5) foi removida','2026-10-09 18:21:37'),(286,73,'INSERT','Nível Cv5 foi cadastrado','2026-10-09 18:22:14'),(287,70,'INSERT','Nível CV4 foi cadastrado','2026-10-09 18:24:14'),(288,73,'INSERT','Matrícula para a data 2026-10-08 foi cadastrada','2026-10-09 18:24:18'),(289,73,'DELETE','Matrícula (ID: 14) da data 2026-10-07 foi removida','2026-10-09 18:24:30'),(290,73,'DELETE','Formando (ID: 7) foi removido','2026-10-09 18:24:51'),(291,70,'INSERT','Campo Gestao foi cadastrado','2026-10-09 18:24:57'),(292,70,'INSERT','Campo Construcao civil foi cadastrado','2026-10-09 18:26:00'),(293,73,'INSERT','Formando Edmundo Mapotere foi cadastrado','2026-10-09 18:26:08'),(294,73,'DELETE','Formando (ID: 8) foi removido','2026-10-09 18:26:54'),(295,70,'INSERT','Qualificação Tecnico de Programacao web foi cadastrada','2026-10-09 18:28:06'),(296,70,'INSERT','Classificação  foi cadastrada','2026-10-09 18:28:07'),(297,70,'INSERT','Associação qualificação/nível (ID: 11) foi cadastrada','2026-10-09 18:28:08'),(298,70,'INSERT','Módulo SUBDS foi cadastrado','2026-10-09 18:29:02'),(299,70,'INSERT','Quali_modulo do semestre 1º Semestre foi cadastrado','2026-10-09 18:29:04'),(300,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-09 18:29:52'),(301,73,'DELETE','Formando (ID: 6) foi removido','2026-10-09 18:30:32'),(302,64,'DELETE','Qualificação (ID: 17) foi removida','2026-10-09 18:31:31'),(303,73,'DELETE','Turma (ID: 9) foi removida','2026-10-09 18:31:46'),(304,73,'DELETE','Turma (ID: 10) foi removida','2026-10-09 18:31:50'),(305,73,'DELETE','Matrícula (ID: 15) da data 2026-10-08 foi removida','2026-10-09 18:31:57'),(306,73,'DELETE','Formando (ID: 5) foi removido','2026-10-09 18:32:07'),(307,64,'INSERT','Qualificação Tecnico de Suporte Informatico foi cadastrada','2026-10-09 18:32:22'),(308,64,'INSERT','Classificação  foi cadastrada','2026-10-09 18:32:23'),(309,64,'INSERT','Associação qualificação/nível (ID: 12) foi cadastrada','2026-10-09 18:32:23'),(310,73,'INSERT','Formando malik mangue foi cadastrado','2026-10-09 18:32:48'),(311,73,'UPDATE','Formando malik malik (ID: 9) foi atualizado','2026-10-09 18:33:19'),(312,64,'UPDATE','Formador Allen Dinis (ID: 16) foi atualizado','2026-10-09 18:33:55'),(313,64,'INSERT','Coordenador Allen foi cadastrado','2026-10-09 18:33:55'),(314,64,'UPDATE','Formador Edmundo Mapotere (ID: 17) foi atualizado','2026-10-09 18:34:05'),(315,64,'INSERT','Diretor de Turma Edmundo foi cadastrado','2026-10-09 18:34:06'),(316,64,'INSERT','Formador Keany Pessula foi cadastrado','2026-10-09 18:35:24'),(317,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-09 18:35:56'),(318,64,'INSERT','Qualificação ATFEA foi cadastrada','2026-10-09 18:36:36'),(319,64,'INSERT','Classificação  foi cadastrada','2026-10-09 18:36:36'),(320,64,'INSERT','Associação qualificação/nível (ID: 13) foi cadastrada','2026-10-09 18:36:36'),(321,64,'INSERT','Sala Sala 1 foi cadastrada','2026-10-09 18:41:07'),(322,64,'INSERT','Sala Sala 2 foi cadastrada','2026-10-09 18:41:24'),(323,72,'LOGIN','Utilizador EMangue iniciou sessão','2026-10-09 18:50:54'),(324,64,'LOGIN','Utilizador admin iniciou sessão','2026-10-09 18:59:14'),(325,70,'LOGIN','Utilizador EMapotere iniciou sessão','2026-10-09 20:10:13');
/*!40000 ALTER TABLE `Log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Matricula`
--

DROP TABLE IF EXISTS `Matricula`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Matricula` (
  `codigo` int NOT NULL AUTO_INCREMENT,
  `cod_formando` int NOT NULL,
  `data` varchar(10) NOT NULL,
  `id_Quali_Nivel` int DEFAULT NULL,
  `cod_Quali` int DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  KEY `cod_formando` (`cod_formando`),
  KEY `fk_Quali_Nivel_Matricula` (`id_Quali_Nivel`),
  KEY `fk_Qualificacao_Matricula` (`cod_Quali`),
  CONSTRAINT `fk_Quali_Nivel_Matricula` FOREIGN KEY (`id_Quali_Nivel`) REFERENCES `Quali_Nivel` (`codigo_Quali_Nivel`),
  CONSTRAINT `fk_Qualificacao_Matricula` FOREIGN KEY (`cod_Quali`) REFERENCES `Qualificacao` (`cod_Quali`),
  CONSTRAINT `Matricula_ibfk_1` FOREIGN KEY (`cod_formando`) REFERENCES `Formando` (`codigo_formando`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Matricula`
--

LOCK TABLES `Matricula` WRITE;
/*!40000 ALTER TABLE `Matricula` DISABLE KEYS */;
/*!40000 ALTER TABLE `Matricula` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Modulo`
--

DROP TABLE IF EXISTS `Modulo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Modulo` (
  `codigo` int NOT NULL AUTO_INCREMENT,
  `nome_modulo` varchar(100) NOT NULL,
  `carga_horaria` int NOT NULL,
  `id_Quali_Nivel` int NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `id_Quali_Nivel` (`id_Quali_Nivel`),
  CONSTRAINT `Modulo_ibfk_1` FOREIGN KEY (`id_Quali_Nivel`) REFERENCES `Quali_Nivel` (`codigo_Quali_Nivel`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Modulo`
--

LOCK TABLES `Modulo` WRITE;
/*!40000 ALTER TABLE `Modulo` DISABLE KEYS */;
INSERT INTO `Modulo` VALUES (6,'SUBDS',120,11);
/*!40000 ALTER TABLE `Modulo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Nivel`
--

DROP TABLE IF EXISTS `Nivel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Nivel` (
  `codigo` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(60) NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Nivel`
--

LOCK TABLES `Nivel` WRITE;
/*!40000 ALTER TABLE `Nivel` DISABLE KEYS */;
INSERT INTO `Nivel` VALUES (8,'CV3'),(9,'Cv4'),(10,'Cv5'),(11,'CV4');
/*!40000 ALTER TABLE `Nivel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Perfil`
--

DROP TABLE IF EXISTS `Perfil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Perfil` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(60) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Perfil`
--

LOCK TABLES `Perfil` WRITE;
/*!40000 ALTER TABLE `Perfil` DISABLE KEYS */;
INSERT INTO `Perfil` VALUES (1,'Operador'),(2,'SuperOperador'),(3,'Administrador'),(4,'Auditor');
/*!40000 ALTER TABLE `Perfil` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Quali_Nivel`
--

DROP TABLE IF EXISTS `Quali_Nivel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Quali_Nivel` (
  `codigo_Quali_Nivel` int NOT NULL AUTO_INCREMENT,
  `cod_Quali` int NOT NULL,
  `cod_Nivel` int NOT NULL,
  PRIMARY KEY (`codigo_Quali_Nivel`),
  KEY `fk_codigo_Qualificacao` (`cod_Quali`),
  KEY `fk_codigo_Nivel` (`cod_Nivel`),
  CONSTRAINT `fk_codigo_Nivel` FOREIGN KEY (`cod_Nivel`) REFERENCES `Nivel` (`codigo`),
  CONSTRAINT `fk_codigo_Qualificacao` FOREIGN KEY (`cod_Quali`) REFERENCES `Qualificacao` (`cod_Quali`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Quali_Nivel`
--

LOCK TABLES `Quali_Nivel` WRITE;
/*!40000 ALTER TABLE `Quali_Nivel` DISABLE KEYS */;
INSERT INTO `Quali_Nivel` VALUES (10,18,8),(11,19,10),(12,20,9),(13,21,8);
/*!40000 ALTER TABLE `Quali_Nivel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Quali_modulo`
--

DROP TABLE IF EXISTS `Quali_modulo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Quali_modulo` (
  `codigo` int NOT NULL AUTO_INCREMENT,
  `cod_modulo` int NOT NULL,
  `cod_Quali` int NOT NULL,
  `semestre` varchar(40) NOT NULL,
  PRIMARY KEY (`codigo`),
  KEY `cod_modulo` (`cod_modulo`),
  KEY `cod_Quali` (`cod_Quali`),
  CONSTRAINT `Quali_modulo_ibfk_1` FOREIGN KEY (`cod_modulo`) REFERENCES `Modulo` (`codigo`),
  CONSTRAINT `Quali_modulo_ibfk_2` FOREIGN KEY (`cod_Quali`) REFERENCES `Qualificacao` (`cod_Quali`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Quali_modulo`
--

LOCK TABLES `Quali_modulo` WRITE;
/*!40000 ALTER TABLE `Quali_modulo` DISABLE KEYS */;
INSERT INTO `Quali_modulo` VALUES (6,6,19,'1º Semestre');
/*!40000 ALTER TABLE `Quali_modulo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Qualificacao`
--

DROP TABLE IF EXISTS `Qualificacao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Qualificacao` (
  `cod_Quali` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(60) NOT NULL,
  `cod_Coordenador` int NOT NULL,
  PRIMARY KEY (`cod_Quali`),
  KEY `fk_cod_Coordenador` (`cod_Coordenador`),
  CONSTRAINT `fk_cod_Coordenador` FOREIGN KEY (`cod_Coordenador`) REFERENCES `Coordenador` (`cod_Formador`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Qualificacao`
--

LOCK TABLES `Qualificacao` WRITE;
/*!40000 ALTER TABLE `Qualificacao` DISABLE KEYS */;
INSERT INTO `Qualificacao` VALUES (18,'Administrador de Redes',14),(19,'Tecnico de Programacao web',14),(20,'Tecnico de Suporte Informatico',14),(21,'ATFEA',16);
/*!40000 ALTER TABLE `Qualificacao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Sala`
--

DROP TABLE IF EXISTS `Sala`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Sala` (
  `codigo` int NOT NULL AUTO_INCREMENT,
  `designacao` varchar(20) NOT NULL,
  `tipo_sala` varchar(20) NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Sala`
--

LOCK TABLES `Sala` WRITE;
/*!40000 ALTER TABLE `Sala` DISABLE KEYS */;
INSERT INTO `Sala` VALUES (2,'Laboratorio B','Laboratório'),(3,'lab 3','Teórica'),(4,'Lab2','Teórica'),(6,'lab 5','Oficina'),(8,'Sala 1','Teórica'),(9,'Sala 2','Teórica');
/*!40000 ALTER TABLE `Sala` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Turma`
--

DROP TABLE IF EXISTS `Turma`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Turma` (
  `codigo` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(40) NOT NULL,
  `ano_lectivo` int NOT NULL,
  `turno` varchar(60) NOT NULL,
  `id_Diretor_Turma` int NOT NULL,
  `id_Quali_Nivel` int DEFAULT '1',
  PRIMARY KEY (`codigo`),
  KEY `fk_id_Diretor` (`id_Diretor_Turma`),
  KEY `fk_codigo_Quali_Nivel` (`id_Quali_Nivel`),
  CONSTRAINT `fk_codigo_Quali_Nivel` FOREIGN KEY (`id_Quali_Nivel`) REFERENCES `Quali_Nivel` (`codigo_Quali_Nivel`),
  CONSTRAINT `fk_id_Diretor` FOREIGN KEY (`id_Diretor_Turma`) REFERENCES `Diretor_Turma` (`cod_Formador`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Turma`
--

LOCK TABLES `Turma` WRITE;
/*!40000 ALTER TABLE `Turma` DISABLE KEYS */;
/*!40000 ALTER TABLE `Turma` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Usuario`
--

DROP TABLE IF EXISTS `Usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Usuario` (
  `idUser` int NOT NULL AUTO_INCREMENT,
  `idPerfil` int NOT NULL,
  `nome` varchar(100) NOT NULL,
  `username` varchar(60) NOT NULL,
  `apelido` varchar(60) NOT NULL,
  `estadoCivil` varchar(40) DEFAULT NULL,
  `genero` varchar(40) DEFAULT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `BI` varchar(30) DEFAULT NULL,
  `password` varchar(60) NOT NULL,
  `primeiroAcesso` tinyint(1) NOT NULL,
  PRIMARY KEY (`idUser`),
  UNIQUE KEY `username` (`username`),
  KEY `idPerfil` (`idPerfil`),
  CONSTRAINT `Usuario_ibfk_1` FOREIGN KEY (`idPerfil`) REFERENCES `Perfil` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Usuario`
--

LOCK TABLES `Usuario` WRITE;
/*!40000 ALTER TABLE `Usuario` DISABLE KEYS */;
INSERT INTO `Usuario` VALUES (64,3,'admin','admin','admin','Solteiro(a)','Masculino','123456789','admin@gmail.com','65432187111','$2a$12$anDhPXGLws2CvuCl1jXZjeHAPt3eBpjIH/DZgPn5XuPHQvLBYEvpu',0),(65,2,'Kenny','KPessula','Pessula','Solteiro(a)','Masculino','851677253','kenny@gmail.com','765432116354','$2y$10$peAmihCszs28Hf7dtH1Co.EVzcTyJnERNMJCgngCswddPECcsMRkm',0),(69,1,'Operador','OMini','mini',NULL,NULL,NULL,NULL,NULL,'$2y$10$a5gxN4QS1v91CNE6d6GSZOkIz7mBNtqv8QWKwBfrS9SXY6/Krq/q2',0),(70,1,'Edmundo','EMapotere','Mapotere','Solteiro(a)','Masculino','875332564','Edmundo@gmail.com','11234567853','$2y$10$t7be58dpr0jl57sB3XxLBumepl5QYVJhBjqeKmJLBbGrmLljoIzTa',0),(71,4,'auditor','auditor','auditor',NULL,NULL,NULL,NULL,NULL,'$2y$10$Lrz3r3h3tpJXLTZbL.Jt/.xkMjw5H.d6hENT5i3CCZWNfXwsiZLJ.',0),(72,4,'Edson','EMangue','Mangue','Solteiro(a)','Masculino','876543212','EdsonMangue@gmail.com','1234567887611','$2y$10$WRR2TGDnfRhpfGAKx7wFB.G7j5iPOlRX4t3TKkfzITl3DeLPjDMw.',0),(73,2,'Fernando','fernando','Mapotere','Casado(a)','Masculino','846533793','fernando@gmail.com','1132456746S','$2y$10$rgYiH5dXoeb7prYh9ouviOn1ja46n.z176e4reRq3Tu0zuqudh9ku',0);
/*!40000 ALTER TABLE `Usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perfil_permissao`
--

DROP TABLE IF EXISTS `perfil_permissao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `perfil_permissao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `perfil_id` int NOT NULL,
  `recurso_id` int NOT NULL,
  `permissao_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_perfil_recurso_permissao` (`perfil_id`,`recurso_id`,`permissao_id`),
  KEY `fk_pp_recurso` (`recurso_id`),
  KEY `fk_pp_permissao` (`permissao_id`),
  CONSTRAINT `fk_pp_perfil` FOREIGN KEY (`perfil_id`) REFERENCES `Perfil` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pp_permissao` FOREIGN KEY (`permissao_id`) REFERENCES `permissao` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pp_recurso` FOREIGN KEY (`recurso_id`) REFERENCES `recurso` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perfil_permissao`
--

LOCK TABLES `perfil_permissao` WRITE;
/*!40000 ALTER TABLE `perfil_permissao` DISABLE KEYS */;
/*!40000 ALTER TABLE `perfil_permissao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissao`
--

DROP TABLE IF EXISTS `permissao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissao`
--

LOCK TABLES `permissao` WRITE;
/*!40000 ALTER TABLE `permissao` DISABLE KEYS */;
INSERT INTO `permissao` VALUES (1,'consultar'),(2,'criar'),(3,'editar'),(4,'eliminar');
/*!40000 ALTER TABLE `permissao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurso`
--

DROP TABLE IF EXISTS `recurso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recurso` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `grupo` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurso`
--

LOCK TABLES `recurso` WRITE;
/*!40000 ALTER TABLE `recurso` DISABLE KEYS */;
INSERT INTO `recurso` VALUES (1,'dashboard','Academico'),(2,'formadores','Academico'),(3,'formandos','Academico'),(4,'matriculas','Academico'),(5,'inscricoes','Academico'),(6,'turmas','Academico'),(7,'modulos','Academico'),(8,'qualificacoes','Academico'),(9,'niveis','Academico'),(10,'campos','Academico'),(11,'salas','Academico'),(12,'licoes','Academico'),(13,'utilizadores','Administrativos'),(14,'logs','Administrativos'),(15,'perfis','Administrativos');
/*!40000 ALTER TABLE `recurso` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-10  5:47:13
