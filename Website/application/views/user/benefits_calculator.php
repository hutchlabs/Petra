<link href="http://www.petratrust.com/wp-content/themes/petra/Aristo.css" rel="stylesheet" type="text/css" />
<form name="form1" id="form1" method="post" action="" class="calfrm">
                        
                          <fieldset>
                            <p style="padding:0; margin:0;"><small style="font-size:10px;">All fields marked * are required</small></p><table width="100%">
                            <tr>
                              <td width="48%"><label>Current Age*</label></td>
                              <td width="52%" class="padd"><input name="cage" type="text" class="text" id="cage"></td>
                            </tr>
                            <tr>
                              <td><label>Retirement Age*</label></td>
                              <td class="padd"><input name="rage" type="text" class="text" id="rage"></td>
                            </tr>
                            <tr>
                              <td><label>Years to Retirement</label></td>
                              <td class="padd"><input name="yretire" type="text" disabled="disabled" class="text disabled" id="yretire" readonly></td>
                            </tr>
                            <tr>
                              <td><label>Current Monthly Salary (GHC)*</label></td>
                              <td class="padd"><input name="csal" type="text" class="text" id="csal"></td>
                            </tr>
                            <tr>
                              <td><label>Portion of Salary Invested</label></td>
                              <td class="padd"><table width="100%">
                                <tr>
                                  <td width="7%" align="left"><span class="range-label">5%</span></td>
                                  <td width="85%" align="center"><input name="psal" class="disable" type="text" id="psal" readonly></td>
                                  <td width="8%" align="right"><span class="range-label">21.5%</span></td>
                                </tr>
                                <tr>
                                  <td colspan="3"><span id="psal_slider"></span></td>
                                </tr>
                              </table></td>
                            </tr>
                            <tr>
                              <td><label>Monthly Contribution (GHC)</label></td>
                              <td style=" padding:5px 0 10px 0;"><input name="acon" type="text" disabled="disabled" class="text disabled" id="acon" readonly></td>
                            </tr>
                            <tr>
                              <td><label>Projected Average Annual Salary Growth</label></td>
                              <td style=" padding:5px 0 10px 0;"><table width="100%">
                                <tr>
                                  <td width="7%" align="left"><span class="range-label">0%</span></td>
                                  <td width="85%" align="center"><input name="pgro"  class="disable" type="text" id="pgro" readonly></td>
                                  <td width="8%" align="right"><span class="range-label">50%</span></td>
                                </tr>
                                <tr>
                                  <td colspan="3"><span id="pgro_slider"></span></td>
                                </tr>
                              </table></td>
                            </tr>
                            <tr>
                              <td><label>Projected Average Investment Returns</label></td>
                              <td style=" padding:5px 0 10px 0;"><table width="100%">
                                <tr>
                                  <td width="7%" align="left"><span class="range-label">0%</span></td>
                                  <td width="85%" align="center"><input name="pret"  class="disable" type="text" id="pret" readonly></td>
                                  <td width="8%" align="right"><span class="range-label">30%</span></td>
                                </tr>
                                <tr>
                                  <td colspan="3"><span id="pret_slider"></span></td>
                                </tr>
                              </table></td>
                            </tr>
                            <tr>
                              <td>&nbsp;</td>
                              <td style="padding:5px 0 10px 0;"><input name="btn"  type="button" class="btn btn2" id="btn" value="   Calculate  &rsaquo;&rsaquo;  "></td>
                            </tr>
                            <tr>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                            </tr>
                            </table>
                            <div id="results2" style="display:none;">
                            <table width="100%" >
                                <tr>
                                  <td width="48%"><label>Lump Sum:</label></td>
                                  <td><span id="lumpsum"></span></td>
                                </tr>
                             </table>
                            </div>
                            <div id="results" style="display:none;">
                                <table width="100%" >
                                <tr>
                                  <td><div id="chartContainer">Loading...</div></td>
                                </tr>
                              </table>
                          </div>
                          </fieldset>
                        </form>

