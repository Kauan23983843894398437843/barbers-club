<?php
/**
 * Classe para gerenciar operações de serviços
 */

require_once __DIR__ . '/db.php';

class Servicos {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtém todas as categorias de serviços
     * 
     * @return array Categorias de serviços
     */
    public function getCategorias() {
        return $this->db->fetchAll(
            "SELECT * FROM categorias_servicos ORDER BY nome"
        );
    }
    
    /**
     * Obtém todos os serviços de uma barbearia
     * 
     * @param int $id_barbearia ID da barbearia
     * @param bool $apenasAtivos Se true, retorna apenas serviços ativos
     * @return array Serviços da barbearia
     */
    public function getServicosBarbearia($id_barbearia, $apenasAtivos = false) {
        $sql = "SELECT s.*, c.nome as categoria_nome 
                FROM servicos s 
                JOIN categorias_servicos c ON s.id_categoria = c.id_categoria 
                WHERE s.id_barbearia = ?";
                
        if ($apenasAtivos) {
            $sql .= " AND s.ativo = 1";
        }
        
        $sql .= " ORDER BY c.nome, s.nome";
        
        return $this->db->fetchAll($sql, [$id_barbearia]);
    }
    
    /**
     * Obtém um serviço específico
     * 
     * @param int $id_servico ID do serviço
     * @param int $id_barbearia ID da barbearia (para verificação)
     * @return array|false Dados do serviço ou false se não encontrado
     */
    public function getServico($id_servico, $id_barbearia) {
        return $this->db->fetch(
            "SELECT s.*, c.nome as categoria_nome 
             FROM servicos s 
             JOIN categorias_servicos c ON s.id_categoria = c.id_categoria 
             WHERE s.id_servico = ? AND s.id_barbearia = ?",
            [$id_servico, $id_barbearia]
        );
    }
    
    /**
     * Adiciona um novo serviço
     * 
     * @param int $id_barbearia ID da barbearia
     * @param array $dados Dados do serviço
     * @return int|false ID do serviço adicionado ou false em caso de erro
     */
    public function adicionarServico($id_barbearia, $dados) {
        $dados['id_barbearia'] = $id_barbearia;
        return $this->db->insert('servicos', $dados);
    }
    
    /**
     * Atualiza um serviço existente
     * 
     * @param int $id_servico ID do serviço
     * @param int $id_barbearia ID da barbearia (para verificação)
     * @param array $dados Dados a serem atualizados
     * @return bool Sucesso da operação
     */
    public function atualizarServico($id_servico, $id_barbearia, $dados) {
        return $this->db->update(
            'servicos',
            $dados,
            'id_servico = ? AND id_barbearia = ?',
            [$id_servico, $id_barbearia]
        ) > 0;
    }
    
    /**
     * Remove (desativa) um serviço
     * 
     * @param int $id_servico ID do serviço
     * @param int $id_barbearia ID da barbearia (para verificação)
     * @return bool Sucesso da operação
     */
    public function removerServico($id_servico, $id_barbearia) {
        return $this->db->update(
            'servicos',
            ['ativo' => 0],
            'id_servico = ? AND id_barbearia = ?',
            [$id_servico, $id_barbearia]
        ) > 0;
    }
    
    /**
     * Verifica se um serviço tem agendamentos futuros
     * 
     * @param int $id_servico ID do serviço
     * @return bool True se o serviço tem agendamentos futuros
     */
    public function temAgendamentosFuturos($id_servico) {
        $hoje = date('Y-m-d');
        
        $agendamentos = $this->db->fetch(
            "SELECT COUNT(*) as total 
             FROM agendamentos 
             WHERE id_servico = ? AND data_agendamento >= ? AND status IN ('agendado', 'confirmado')",
            [$id_servico, $hoje]
        );
        
        return $agendamentos['total'] > 0;
    }
}
?>