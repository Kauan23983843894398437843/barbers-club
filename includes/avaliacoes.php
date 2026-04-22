<?php
/**
 * Classe para gerenciar operações de avaliações
 */

require_once __DIR__ . '/db.php';

class Avaliacoes {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtém as avaliações de uma barbearia
     * 
     * @param int $id_barbearia ID da barbearia
     * @param int $limite Limite de avaliações a serem retornadas (0 = sem limite)
     * @param int $pagina Página atual para paginação
     * @return array Avaliações da barbearia
     */
    public function getAvaliacoesBarbearia($id_barbearia, $limite = 0, $pagina = 1) {
        $sql = "SELECT a.*, 
                       c.nome as cliente_nome, 
                       c.sobrenome as cliente_sobrenome,
                       c.foto_perfil as cliente_foto,
                       br.nome as barbeiro_nome, 
                       br.sobrenome as barbeiro_sobrenome 
                FROM avaliacoes a 
                JOIN clientes c ON a.id_cliente = c.id_cliente 
                JOIN barbeiros br ON a.id_barbeiro = br.id_barbeiro 
                WHERE a.id_barbearia = ? 
                ORDER BY a.data_avaliacao DESC";
        
        $params = [$id_barbearia];
        
        if ($limite > 0) {
            $offset = ($pagina - 1) * $limite;
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $limite;
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtém as avaliações de um barbeiro
     * 
     * @param int $id_barbeiro ID do barbeiro
     * @param int $limite Limite de avaliações a serem retornadas (0 = sem limite)
     * @param int $pagina Página atual para paginação
     * @return array Avaliações do barbeiro
     */
    public function getAvaliacoesBarbeiro($id_barbeiro, $limite = 0, $pagina = 1) {
        $sql = "SELECT a.*, 
                       c.nome as cliente_nome, 
                       c.sobrenome as cliente_sobrenome,
                       c.foto_perfil as cliente_foto 
                FROM avaliacoes a 
                JOIN clientes c ON a.id_cliente = c.id_cliente 
                WHERE a.id_barbeiro = ? 
                ORDER BY a.data_avaliacao DESC";
        
        $params = [$id_barbeiro];
        
        if ($limite > 0) {
            $offset = ($pagina - 1) * $limite;
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $limite;
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Obtém as avaliações feitas por um cliente
     * 
     * @param int $id_cliente ID do cliente
     * @return array Avaliações do cliente
     */
    public function getAvaliacoesCliente($id_cliente) {
        return $this->db->fetchAll(
            "SELECT a.*, 
                    b.nome as barbearia_nome,
                    br.nome as barbeiro_nome, 
                    br.sobrenome as barbeiro_sobrenome 
             FROM avaliacoes a 
             JOIN barbearias b ON a.id_barbearia = b.id_barbearia 
             JOIN barbeiros br ON a.id_barbeiro = br.id_barbeiro 
             WHERE a.id_cliente = ? 
             ORDER BY a.data_avaliacao DESC",
            [$id_cliente]
        );
    }
    
    /**
     * Obtém uma avaliação específica
     * 
     * @param int $id_avaliacao ID da avaliação
     * @return array|false Dados da avaliação ou false se não encontrada
     */
    public function getAvaliacao($id_avaliacao) {
        return $this->db->fetch(
            "SELECT a.*, 
                    c.nome as cliente_nome, 
                    c.sobrenome as cliente_sobrenome,
                    b.nome as barbearia_nome,
                    br.nome as barbeiro_nome, 
                    br.sobrenome as barbeiro_sobrenome 
             FROM avaliacoes a 
             JOIN clientes c ON a.id_cliente = c.id_cliente 
             JOIN barbearias b ON a.id_barbearia = b.id_barbearia 
             JOIN barbeiros br ON a.id_barbeiro = br.id_barbeiro 
             WHERE a.id_avaliacao = ?",
            [$id_avaliacao]
        );
    }
    
    /**
     * Verifica se um cliente já avaliou um agendamento
     * 
     * @param int $id_agendamento ID do agendamento
     * @param int $id_cliente ID do cliente
     * @return bool True se o cliente já avaliou o agendamento
     */
    public function clienteJaAvaliou($id_agendamento, $id_cliente) {
        $avaliacao = $this->db->fetch(
            "SELECT id_avaliacao FROM avaliacoes 
             WHERE id_agendamento = ? AND id_cliente = ?",
            [$id_agendamento, $id_cliente]
        );
        
        return $avaliacao !== false;
    }
    
    /**
     * Adiciona uma avaliação
     * 
     * @param array $dados Dados da avaliação
     * @return int|false ID da avaliação adicionada ou false em caso de erro
     */
    public function adicionarAvaliacao($dados) {
        // Verifica se o cliente já avaliou este agendamento
        if ($this->clienteJaAvaliou($dados['id_agendamento'], $dados['id_cliente'])) {
            return false;
        }
        
        // Adiciona a data da avaliação
        $dados['data_avaliacao'] = date('Y-m-d H:i:s');
        
        // Insere a avaliação
        return $this->db->insert('avaliacoes', $dados);
    }
    
    /**
     * Adiciona uma resposta a uma avaliação
     * 
     * @param int $id_avaliacao ID da avaliação
     * @param string $resposta Texto da resposta
     * @param int $id_barbearia ID da barbearia (para verificação)
     * @return bool Sucesso da operação
     */
    public function responderAvaliacao($id_avaliacao, $resposta, $id_barbearia) {
        // Verifica se a avaliação pertence à barbearia
        $avaliacao = $this->getAvaliacao($id_avaliacao);
        
        if (!$avaliacao || $avaliacao['id_barbearia'] != $id_barbearia) {
            return false;
        }
        
        // Atualiza a avaliação com a resposta
        return $this->db->update(
            'avaliacoes',
            [
                'resposta' => $resposta,
                'data_resposta' => date('Y-m-d H:i:s')
            ],
            'id_avaliacao = ?',
            [$id_avaliacao]
        ) > 0;
    }
    
    /**
     * Calcula a média de avaliações de uma barbearia
     * 
     * @param int $id_barbearia ID da barbearia
     * @return float Média das avaliações
     */
    public function getMediaBarbearia($id_barbearia) {
        $media = $this->db->fetch(
            "SELECT AVG(nota) as media FROM avaliacoes WHERE id_barbearia = ?",
            [$id_barbearia]
        );
        
        return $media ? round($media['media'], 1) : 0;
    }
    
    /**
     * Calcula a média de avaliações de um barbeiro
     * 
     * @param int $id_barbeiro ID do barbeiro
     * @return float Média das avaliações
     */
    public function getMediaBarbeiro($id_barbeiro) {
        $media = $this->db->fetch(
            "SELECT AVG(nota) as media FROM avaliacoes WHERE id_barbeiro = ?",
            [$id_barbeiro]
        );
        
        return $media ? round($media['media'], 1) : 0;
    }
    
    /**
     * Obtém o total de avaliações de uma barbearia
     * 
     * @param int $id_barbearia ID da barbearia
     * @return int Total de avaliações
     */
    public function getTotalAvaliacoesBarbearia($id_barbearia) {
        $total = $this->db->fetch(
            "SELECT COUNT(*) as total FROM avaliacoes WHERE id_barbearia = ?",
            [$id_barbearia]
        );
        
        return $total ? $total['total'] : 0;
    }
    
    /**
     * Obtém o total de avaliações de um barbeiro
     * 
     * @param int $id_barbeiro ID do barbeiro
     * @return int Total de avaliações
     */
    public function getTotalAvaliacoesBarbeiro($id_barbeiro) {
        $total = $this->db->fetch(
            "SELECT COUNT(*) as total FROM avaliacoes WHERE id_barbeiro = ?",
            [$id_barbeiro]
        );
        
        return $total ? $total['total'] : 0;
    }
}
?>